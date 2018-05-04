<?php

// Super News Feeder :)
// CLI  : php bin/console app:feeder
// CRON : php bin/console app:feeder > /var/log/feeder.log 2>&1

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;
//use Symfony\Component\Lock\Store\SemaphoreStore;

use FeedIo\FeedIo;
use FeedIo\Adapter\Guzzle\Client;
use GuzzleHttp\Client as GuzzleClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;

use App\Entity\News;

// class FeederCommand extends Command
class FeederCommand extends ContainerAwareCommand
{
    private $feeds = [
        'bitcoin'       => 'https://news.bitcoin.com/feed/',
        'dailyhodl'     => 'https://dailyhodl.com/feed/',
        'cointelegraph' => 'https://cointelegraph.com/rss',
        'bitcoinist'    => 'http://bitcoinist.com/feed/',
        'cryptovest'    => 'https://cryptovest.com/feed/',
        'coindesk'      => 'https://www.coindesk.com/feed/',
    ];

    protected function configure()
    {
        $this
            ->setName('app:feeder')
            ->setDescription('Automatically scans RSS feeds of content providers once per hour to retrieve latest news')
            ->setHelp('This command allows you to scan RSS feeds and get latest news')
            ->addArgument('feed', InputArgument::OPTIONAL, 'The name of certain RSS to scan')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Genereate folder names for images and create them if necessary

        $monthDir = (new \DateTime())->format('Ym');
        $imageDir = $this->getContainer()->get('kernel')->getProjectDir() . '/public/images/news/';

        if (!file_exists ($imageDir . $monthDir))
            if (!mkdir($imageDir . $monthDir, 0777, true))
                die("[ERR] Cant create " . $imageDir . $monthDir . " folder");

        $em = $this->getContainer()->get('doctrine')->getManager();

        $store = new FlockStore(sys_get_temp_dir());
        $factory = new Factory($store);

        $lock = $factory->createLock('news-feeder-lock');
        if (!$lock->acquire())
            die("[ERR] The Feeder instance already running. Please wait before launch another one");

        // Scan all RSS feeds to find latest news
        foreach ($this->feeds as $provider => $url) {

            $date = (new \DateTime())->format('Y-m-d H:i');
            echo "\n\n--- [$date] $provider ---\n";

            $guzzle = new GuzzleClient([ 'verify' => false ]);
            $client = new Client($guzzle);
            // FIXME! Set up Monolog properly https://github.com/Seldaek/monolog
            // Logger::ERROR ignores all INFO, NOTICE and DEBUG messages
            $logger = new Logger('default', [new StreamHandler('php://stdout', Logger::ERROR)]);
            $feeder = new FeedIo($client, $logger);
            $modifiedSince = new \DateTime('-2 hours');

            // Trying to get news feed up to 3 times for max of 3 minutes
            $feed = null; $count = 0;
            while (!$feed) {
                $count ++;
                try {
                    $feed = $feeder->readSince($url, $modifiedSince)->getFeed();
                }
                catch(\Exception $e) {
                    echo("\n[ERR] Can't get news feed from $url for the $count'st try");
                    if ($count > 3) break;
                    else sleep(60);
                }
            }
            if (!$feed) continue; // If there are some network problems. proceed with the next provider

            foreach ($feed as $item) {

                $title = $lead = $text = $image = $tags = $date = $source = '';

                $title = $item->getTitle();

                // NB! After we got item, purge unnecessary tags from it with str_replace
                $lead = trim($item->getDescription());

                $lead = trim(str_replace(
                    ['![CDATA[<', '</p>]]>', '></p>]]', '</p>', '<p>', '#NEWS', '#ANALYSIS', '#SPONSORED', '#RECAP', '#EXPERT_TAKE', '#EXPLAINED]', '#PRICE_ANALYSIS', '#FOLLOW_UP'],
                    ['', '', '', '', '', '', '', '', '', '', '', '', '',],
                    $lead));

                // NB! And after that we have to remove some more complex staff too (divs, images and so on)
                // ...

                $source = $item->getLink();
                $date = $item->getLastModified();

                $tags = "";
                foreach ($item->getCategories() as $tag)
                    $tags .= trim($tag->getLabel()) . ', ';
                $tags = $this->truncate($tags, 200); // news.bitcoin.com returns way tooooo long tags
                $tags = trim($tags, ', ');

                // NB! And we have to get FULL TEXT somewhere
                // ...
                // FIXME Handle errors here!
                $response = $guzzle->get($source);
                $html = (string) $response->getBody();

                // NB! And we have to get IMAGE somewhere too

                if ($item->hasMedia()) {
                    $medias = $item->getMedias();
                    foreach ($medias as $m) {
                        $type = $m->getType();
                        $image = $m->getUrl();
                    }
                }

                if ($provider == 'cointelegraph') {
                    // Remove image from lead
                    preg_match('/<img.*>(.*)/usi', $lead, $matches);
                    $lead = count($matches) ? $matches[1] : '';

                    $crawler = new Crawler($html);
                    $crawler = $crawler->filter('.post-full-text');
                    $text = $crawler->html();

                    // FIXME Remove from the end of HTML : <div id="quiz"></div>
                    $patterns = [ '<div id="quiz">', ];
                    foreach ($patterns as $pattern) {
                        $pos = mb_strpos($text, $pattern);
                        if ($pos) break;
                    }
                    if ($pos) $text = mb_substr($text, 0, $pos);

                }

                if ($provider == 'bitcoinist') {

                    // Narrow search area to header DIV
                    $crawler = new Crawler($html);
                    $crawler = $crawler->filter('.post-header');
                    $head_html = $crawler->html();

                    // Get link for JPEG image
                    //preg_match('/http.*\.(jpg|jpeg|png|webp)/usi', $head_html, $matches);
                    preg_match('/http\S*(jpg|jpeg|png|webp)/usi', $head_html, $matches);
                    $image = count($matches) ? $matches[0] : '';

                    preg_match('/<!-- Content -->(.*)<!-- End Content -->/usi', $html, $matches);
                    $text = count($matches) ? $matches[1] : '';

                }

                if ($provider == 'cryptovest') {

                    // Narrow search area to header DIV
                    $crawler = new Crawler($html);
                    $image = $crawler->filter('.arcticle-start-img')->attr('src');

                    // Narrow search area to header DIV
                    $crawler = new Crawler($html);
                    $crawler = $crawler->filter('.twitterembedcontainer');
                    $text = $crawler->html();

                }

                if ($provider == 'coindesk') {

                    // Narrow search area to header DIV
                    $crawler = new Crawler($html);
                    $head_html = $crawler->filter('.article-top-image-section')->attr('style');

                    //preg_match('/http.*\.(jpg|jpeg|png|webp)/usi', $head_html, $matches);
                    preg_match('/http\S*(jpg|jpeg|png|webp)/usi', $head_html, $matches);
                    $image = count($matches) ? $matches[0] : '';

                    // Narrow search area to header DIV
                    $crawler = new Crawler($html);
                    $crawler = $crawler->filter('.article-content-container');
                    $text = $crawler->html();

                    // Remove trash footer if exists
                    $patterns = [
                        '<div id="om-riuyojisrlntqyiu-holder"></div>'
                    ];
                    foreach ($patterns as $pattern) {
                        $pos = mb_strpos($text, $pattern);
                        if ($pos) break;
                    }
                    if ($pos) $text = mb_substr($text, 0, $pos);

                    // We have to show images with lazy download aka data-lazy-src attribute
                    $lazyJS =
                        '<script data-no-minify="1" data-cfasync="false">(function(w,d){function a(){
                        var b=d.createElement("script");b.async=!0;b.src="https://www.coindesk.com/wp-content/plugins/wp-rocket/inc/front/js/lazyload.1.0.5.min.js";
                        var a=d.getElementsByTagName("script")[0];a.parentNode.insertBefore(b,a)}w.attachEvent?w.attachEvent("onload",a):w.addEventListener("load",a,!1)
                        })(window,document);</script>';
                    $text .= $lazyJS;

                }

                if ($provider == 'dailyhodl') {

                    $crawler = new Crawler($html);
                    $image_html = $crawler->filter('.post-image')->html();
                    //preg_match('/http.*\.(jpg|jpeg|png|webp)/usi', $image_html, $matches);
                    preg_match('/http\S*(jpg|jpeg|png|webp)/usi', $image_html, $matches);
                    $image = count($matches) ? $matches[0] : '';

                    $crawler = new Crawler($html);
                    $text = trim($crawler->filter('.single-post-content-wrap')->html());

                    // Remove ad banner from the inner part of HTML

                    /*
                    <div class="code-block code-block-1" style="margin: 8px auto; text-align: center; clear: both;">
                    <script async src="https://serve.czilladx.com/serve/jslib/fb.js"></script>
                    <!-- Coinzilla Banner 728x90 -->
                    <div class="coinzilla" data-zone="329155a91f3b147fd5" data-w="728" data-h="90" style="max-width: 728px; width:100%; display: inline-block;"></div>
                    </div>
                    */

                    $coinzilla_crawler = $crawler->filter('div .code-block');
                    $domElement = $coinzilla_crawler->getNode(0);
                    $coinzilla_html = $domElement->ownerDocument->saveHTML($domElement); // The ugly way to get outerHTML of the whole adverising DIV here
                    if (mb_strpos($text, 'coinzilla')) {
                        $pos = mb_strpos($text, $coinzilla_html);
                        if ($pos)
                            $text = trim(mb_substr($text, 0, $pos)) . trim(mb_substr($text, $pos + mb_strlen($coinzilla_html)));
                    }

                    // Remove trash footer  if exists
                    $patterns = [
                        '<p style="text-align: center;"><span style="font-size: 10pt; color: #333333;">',
                        '<h6><b>Disclaimer',
                        '<h6>Disclaimer',
                        '<div class="heateorSssClear">',
                    ];
                    foreach ($patterns as $pattern) {
                        $pos = mb_strpos($text, $pattern);
                        if ($pos) break;
                    }
                    if ($pos) $text = mb_substr($text, 0, $pos);

                }

                if ($provider == 'bitcoin') {

                    // We have to remove image and other staff from lead
                    preg_match('/<img.*\/>/usi', $lead, $matches);
                    $img_html = $matches[0];
                    $pos = mb_strpos($lead, $img_html);
                    if ($pos >= 0)
                        $lead = trim(mb_substr($lead, 0, $pos)) . trim(mb_substr($lead, $pos + mb_strlen($img_html)));

                    // Remove trash from Lead
                    $patterns = [ 'Also read', 'The post <a' ];
                    foreach ($patterns as $pattern) {
                        $pos = mb_strpos($lead, $pattern);
                        if ($pos) break;
                    }
                    if ($pos) $lead = trim(mb_substr($lead, 0, $pos));

                    $crawler = new Crawler($html);
                    $image_html = $crawler->filter('.td-post-featured-image')->html();
                    // preg_match('/http.*\.(jpg|jpeg|png|webp)/usi', $image_html, $matches);
                    preg_match('/http\S*(jpg|jpeg|png|webp)/usi', $image_html, $matches);
                    $image = count($matches) ? $matches[0] : '';

                    $crawler = new Crawler($html);
                    $text = trim($crawler->filter('.td-post-content')->html());

                    // Remove trash header if exists
                    $patterns = [ '<p>', ];
                    foreach ($patterns as $pattern) {
                        $pos = mb_strpos($text, $pattern);
                        if ($pos) break;
                    }
                    if ($pos) $text = mb_substr($text, $pos);

                    // Remove trash footer if exists
                    $patterns = [ '<p><i>Need to calculate', ];
                    foreach ($patterns as $pattern) {
                        $pos = mb_strpos($text, $pattern);
                        if ($pos) break;
                    }
                    if ($pos) $text = mb_substr($text, 0, $pos);

                }

                // Are there YouTube video? Style it with .videoWrapper
                $matches = preg_grep('/<iframe.*(youtube.com|youtu.be)*iframe>/usi', [ $text ]);
                if (count($matches)) {
                    $text = preg_replace('/(<iframe)/usi', '<div class="videoWrapper"><iframe', $text);
                    $text = preg_replace('/(iframe>)/usi', 'iframe></div>', $text);
                }

                // NB! Set provider / THAT FOR LATER!
                // ...

                // Replace HTTP links to HTTPS to avoid 'broken' images, etc
                $text = preg_replace('/(http:\/\/)/usi', 'https://', $text);

                echo "\n$source";

                // Some dumb checks to avoid EMPTY news / NB! Log that with WARNING message later
                if (strlen($image) <= 10 || strlen($text) <= 100) {
                    echo " [SKIPPED]";
                    continue;
                }

                $news = new News();
                $news->setTitle($title);
                $news->setLead($lead);
                $news->setText($text);
                $news->setTags($tags);
                $news->setSource($source);
                $news->setDate($date);
                $news->setActive(false);

                $path_parts = pathinfo($image);
                $ext = $path_parts['extension'];
                $newImage = $monthDir . '/' . uniqid() . '.' . $ext;
                $newImageFull = $imageDir . $newImage;

                // If we could copy remote image to our server, set it as the origin for our image
                if (copy ($image, $newImageFull))
                    $news->setImage($newImage);
                else
                    $news->setImage($image);

                // Trying to store news into the DB
                try {
                    $em->persist($news);
                    $em->flush();
                    echo " [NEW]";
                }
                // Is it already stored in DB ? Continue to the next record or die in any other case
                catch (\Exception $e) {
                    if (strpos($e, 'SQLSTATE[23000]')) {
                        echo " [DUPLICATE]";
                        unlink($newImageFull);
                        // После исключения Entity Manager закрывается, надо открывать новое соединение
                        $em = $this->getContainer()->get('doctrine')->getManager();
                        if (!$em->isOpen())
                            $em = $em->create($em->getConnection(), $em->getConfiguration());
                        continue;
                    }
                    else {
                        echo $e->getMessage();
                        die();
                    }
                }

            }
        }

        $lock->release();
    }

    // Truncate strings longer then we need by words

    function truncate($text, $chars = 100) {

        if (mb_strlen($text) <= $chars)
            return $text;

        $text = $text . ' ';
        $text = mb_substr($text, 0, $chars);
        $text = mb_substr($text, 0, mb_strrpos($text, ' '));

        //$text = $text."...";

        return $text;
    }

}
