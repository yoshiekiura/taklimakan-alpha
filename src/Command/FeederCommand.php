<?php

// News Feeder
// CLI : php bin/console app:feeder
// CRON :

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

            echo "\n\n--- [PROVIDER] $provider ---\n";

            $guzzle = new GuzzleClient([ 'verify' => false ]);
            $client = new Client($guzzle);
            // FIXME! Set up Monolog properly https://github.com/Seldaek/monolog
            // Logger::ERROR ignores all INFO, NOTICE and DEBUG messages
            $logger = new Logger('default', [new StreamHandler('php://stdout', Logger::ERROR)]);
            $feeder = new FeedIo($client, $logger);
            $modifiedSince = new \DateTime('-2 hours');

            // Trying to get news feed up to 3 times
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
                    ['![CDATA[<', '</p>]]>', '></p>]]', '</p>', '<p>', '#NEWS', '#ANALYSIS', '#SPONSORED', '#RECAP', '#EXPERT_TAKE', '#EXPLAINED]', ],
                    ['', '', '', '', '', '', '', '', '', '', '', ],
                    $lead));

                // NB! And after that we have to remove some more complex staff too (divs, images and so on)
                // ...

                $source = $item->getLink();
                $date = $item->getLastModified();

                $tags = "";
                foreach ($item->getCategories() as $tag)
                    $tags .= trim($tag->getLabel()) . ', ';
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

    //var_dump($text_html);
    //die();
                }

                if ($provider == 'bitcoinist') {

                    // Narrow search area to header DIV
                    $crawler = new Crawler($html);
                    $crawler = $crawler->filter('.post-header');
                    $head_html = $crawler->html();

                    // Get link for JPEG image
                    // preg_match('/<!-- Image Wrap -->.*(http.*\.jpg).*<!-- End Image Wrap -->/usi', $html, $matches);
                    preg_match('/http.*\.(jpg|jpeg|png)/usi', $head_html, $matches);
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

                    preg_match('/http.*\.(jpg|jpeg|png)/usi', $head_html, $matches);
                    $image = count($matches) ? $matches[0] : '';

                    // Narrow search area to header DIV
                    $crawler = new Crawler($html);
                    $crawler = $crawler->filter('.article-content-container');
                    $text = $crawler->html();

                }

                // NB! Set provider / THAT FOR LATER!
                // ...

                echo "\n$source";

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
//var_dump($newImage);
//var_dump($newImageFull);
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

}
