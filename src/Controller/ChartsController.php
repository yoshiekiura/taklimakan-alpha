<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

//use Symfony\Component\Form\AbstractType;
//use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\OptionsResolver\OptionsResolver;

class ChartsController extends Controller
{
    /**
     * @Route("/charts", name="charts")
     */
    public function index()
    {

//        $ChartsRepo = $this->getDoctrine()->getRepository(Charts::class);
//        $Charts = $ChartsRepo->findAll();
//var_dump($Charts);
//die();
        // $greeting = $generator->getRandomGreeting();
        // $logger->info("Saying $greeting to $name!");

        //        $logger->info("Saying hello to $name!");
        //		return new Response("Hello $name!");

        return $this->render('charts/index.html.twig', [
            //'controller_name' => 'ChartsController',
            'menu' => 'charts',
            'charts' => $charts,
        ]);
    }

    /* @ Route("api/charts/{type}", name="api_charts")
    public function getData($type, Request $request) */

    /**
     * @Route("api/charts/all", name="api_charts")
     */
    public function getData(Request $request)
    {
        $symbol = $request->query->get('symbol');

        // $params['color'] = blue;
        // $query->execute($params);

        $sql = 'SELECT * FROM numerical_analytics WHERE type_id = "1" AND pair = "BTC-USD"';
        $query = $this->getDoctrine()->getConnection()->prepare($sql);
        $query->execute();

        $rows = $query->fetchAll();

        $data = [];
        foreach ($rows as $row)
            $data[] = [ $row['dt'], $row['value'] ];

        $response = new JsonResponse(json_encode($data));

        return $response;

        //var_dump($rows);
        //die();

                //$params['symbol'] = $symbol;
        /*        $data = [
                    ['2018-01-01', 10000],
                    ['2018-02-02', 8000],
                    ['2018-03-03', 7000],
                ];
        */


//        $ChartsRepo = $this->getDoctrine()->getRepository(Charts::class);
//        $Charts = $ChartsRepo->findAll();
//var_dump($Charts);
//die();
        // $greeting = $generator->getRandomGreeting();
        // $logger->info("Saying $greeting to $name!");

        //        $logger->info("Saying hello to $name!");
        //		return new Response("Hello $name!");


//var_dump($request);
//var_dump($type);
//var_dump($symbol);
//die();

//        return $this->render('charts/price.html.twig', [
//            //'controller_name' => 'ChartsController',
//            'params' => $params,
//            'data' => $data,
//        ]);
    }

    /**
     * @Route("/charts/{type}", name="price_chart")
     */
    public function showChart($type, Request $request)
    {
        $conn = $this->getDoctrine()->getConnection();

        $sql = 'SELECT data FROM pair_set WHERE id = "1"';
        $query = $conn->prepare($sql);
        $query->execute();
        $json = $query->fetchColumn();
        $allowed = json_decode($json);
//var_dump($allowed);
//die();

/*
        $symbol = $request->query->get('symbol');
        if (!in_array($symbol, $allowedSymbols))
            $symbol = "BTC";
        $pair = "$symbol-USD";
*/
        $pair = $request->query->get('pair');
        if (!in_array($pair, $allowed))
            $pair = "BTC-USD";

        $params['pair'] = $pair;

//        function get_msft_daily_short_data() {
//          return [
//            ['2004-01-02', 27.58, 27.77, 27.33, 27.45, 44487700],
/*
        $data = [
            ['2004-01-02', 27.58, 27.77, 27.33, 27.45, 44487700],
            ['2004-01-05', 27.73, 28.18, 27.72, 28.14, 67333696],
            ['2004-01-06', 28.19, 28.28, 28.07, 28.24, 46950800],
            ['2004-01-07', 28.17, 28.31, 28.01, 28.21, 54298200],
            ['2004-01-08', 28.39, 28.48, 28, 28.16, 58810800]
        ];

        $data = file_get_contents('http://localhost/api/charts/price?symbol=BTC');
*/

        // --- Price 1 -------------------------------------------------

        $sql = 'SELECT * FROM numerical_analytics WHERE type_id = "1" AND pair = "' . $pair . '"';
        $query = $conn->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();

        // --- Volume 2 -------------------------------------------------

        $sql = 'select * from numerical_analytics where type_id="2" and pair="' . $pair . '"';
        $query = $conn->prepare($sql);
        $query->execute();
        $volumeRows = $query->fetchAll();

        // --- Data = Price + Volume -------------------------------------------------

        $data = [];
        foreach ($rows as $row) {
            $date = substr($row['dt'], 0, 10);
            $price = floatval($row['value']);
            $volume = 0;
            foreach ($volumeRows as $vol)
                if ($date == substr($vol['dt'], 0, 10)) {
                    $volume = $vol['value'];
                    break;
                }
            $data[] = [ $date, $price, $volume ];
        }

        // --- Volatility 3 -------------------------------------------------

        $sql = 'SELECT * FROM numerical_analytics WHERE type_id = "3" AND pair = "' . $pair . '"';
        $query = $this->getDoctrine()->getConnection()->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();
        $volatility = [];
        foreach ($rows as $row)
            $volatility[] = [ substr($row['dt'], 0, 10), floatval($row['value']) ];

        // --- Alpha 4 and 8 -------------------------------------------------

        $sql = 'SELECT * FROM numerical_analytics WHERE type_id = "4" AND pair = "' . $pair . '"';
        $query = $this->getDoctrine()->getConnection()->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();
        $alpha = [];
        foreach ($rows as $row)
            $alpha[] = [ substr($row['dt'], 0, 10), floatval($row['value']) ];

        // --- Beta 5 and 9 -------------------------------------------------

        $sql = 'SELECT * FROM numerical_analytics WHERE type_id = "5" AND pair = "' . $pair . '"';
        $query = $this->getDoctrine()->getConnection()->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();
        $beta = [];
        foreach ($rows as $row)
            $beta[] = [ substr($row['dt'], 0, 10), floatval($row['value']) ];

        // --------------------------------------------------------

        // --- Sharpe 6 -------------------------------------------------

        $sql = 'SELECT * FROM numerical_analytics WHERE type_id = "6" AND pair = "' . $pair . '"';
        $query = $this->getDoctrine()->getConnection()->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();
        $sharpe = [];
        foreach ($rows as $row)
            $sharpe[] = [ substr($row['dt'], 0, 10), floatval($row['value']) ];

        // --------------------------------------------------------

        // --- Index 11 -------------------------------------------------

        $sql = 'SELECT * FROM numerical_analytics WHERE type_id = "11" AND pair = "INDEX001"';
        $query = $this->getDoctrine()->getConnection()->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();
        $crypto_index = [];
        foreach ($rows as $row)
            $crypto_index[] = [ substr($row['dt'], 0, 10), floatval($row['value']) ];

        // --------------------------------------------------------

        return $this->render('charts/all.html.twig', [
            'menu' => 'charts',
            'params' => $params,
            'allowed' => $allowed,
            'pair' => $pair,
            'data' => $data,
            'volatility' => $volatility,
            'alpha' => $alpha,
            'beta' => $beta,
            'sharpe' => $sharpe,
            'crypto_index' => $crypto_index,
            'show_charts' => true,
        ]);

//var_dump($data);
//die();

    }


}
