<?php

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

        $em = $this->getContainer()->get('doctrine')->getManager();

//var_dump(openssl_get_cert_locations());
//die();
//..curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);

        //$store = new SemaphoreStore();
        $store = new FlockStore(sys_get_temp_dir());
        $factory = new Factory($store);

        $lock = $factory->createLock('pdf-invoice-generation');

        if (!$lock->acquire())
            die("[ERR] The Feeder instance already running. Please wait before launch another one");

        // Scan all RSS feeds to find latest news
        foreach ($this->feeds as $provider => $url) {

echo "\n\n --- $provider ---------------------------------------------------------------------------------- \n";

            $guzzle = new GuzzleClient([ 'verify' => false ]);
            $client = new Client($guzzle);
            // FIXME! Set up Monolog properly https://github.com/Seldaek/monolog
            // Logger::ERROR ignores all INFO, NOTICE and DEBUG messages
            $logger = new Logger('default', [new StreamHandler('php://stdout', Logger::ERROR)]);
            $feeder = new FeedIo($client, $logger);

            // $feedIo = $this->getContainer()->get('feedio');
            $modifiedSince = new \DateTime('-12 hours');
            // $feed = $feedIo->read($url, new \Acme\Entity\Feed, $modifiedSince)->getFeed();
            //$feed = $feedIo->read($url)->getFeed();

            $feed = $feeder->readSince($url, $modifiedSince)->getFeed();

            foreach ($feed as $item) {

//var_dump($item);

                $title = $item->getTitle();

                // NB! After we got item, purge unnecessary tags from it with str_replace
                $lead = trim($item->getDescription());
echo "\nLEAD-FULL = $lead";
                $lead = trim(str_replace(
                    ['![CDATA[<', '</p>]]>', '></p>]]', '</p>', '<p>', '#NEWS', '#ANALYSIS', '#SPONSORED', '#RECAP', '#EXPERT_TAKE', '#EXPLAINED]', ],
                    [''         , '',        '',        '',    '',    '',      '',          '',           '',        '',            '',            ],
                    $lead));
echo "\nLEAD-TRIM = $lead";
                // NB! And after that we have to remove some more complex staff too (divs, images and so on)
                // ...

                $source = $item->getLink();
                //$date = $item->getLastModified()->format("Y-m-d H:i:s");
                $date = $item->getLastModified();
                //$date = $item->getDate();

//var_dump($lead);

//echo "\nTITLE = $title";
//echo "\nLEAD = $lead";
echo "\nSOURCE = $source";
//echo "\nDATE = $date";

//echo "[TAGS]";
                $tags = "";
                foreach ($item->getCategories() as $tag)
                    $tags .= trim($tag->getLabel()) . ', ';
                $tags = trim($tags, ', ');

            // NB! And we have to get FULL TEXT somewhere
            // ...
            // FIXME Handle errors here!
            $response = $guzzle->get($source);
            $html = (string) $response->getBody();
//var_dump($html);

            $crawler = new Crawler($html);

            $image = $crawler->filter('body div .post-header .height--container"')->attr('style');
var_dump($image);
//var_dump($crawler->filter('body')->children());
die();


            // NB! And we have to get IMAGE somewhere too
            // ...

            // NB! Set provider / THAT FOR LATER!
            // ...

            // Are there the exact same News in the DB already? If yes, just skip it
            // ...

            // If no, save the news item to DB

            $news = new News();
            $news->setTitle($title);
            $news->setLead($lead);
            $news->setTags($tags);
            $news->setSource($source);
            $news->setDate($date);
            //$news->

            $em->persist($news);
            $em->flush();

//var_dump($tags);

//echo "\nTAGS = $tags";

//    var_dump($v->getLabel());

//}


        if ($item->hasMedia()) {
            $medias = $item->getMedias();
            foreach ($medias as $m) {
                //var_dump($m);
                $type = $m->getType();
                echo "\nMEDIA-TYPE $type";
                $url = $m->getUrl();
                echo "\nMEDIA-URL $url";

            }
        }

//var_dump($medias);
//die();
            }

        }

        $lock->release();
    }
}
