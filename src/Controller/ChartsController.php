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
            'charts' => $charts,
        ]);
    }

    /**
     * @Route("api/charts/{type}", name="api_charts")
     */
    public function getData($type, Request $request)
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
        $symbol = $request->query->get('symbol');

        $params['symbol'] = $symbol;

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
        $sql = 'SELECT * FROM numerical_analytics WHERE type_id = "3" AND pair = "BTC-USD"';
        $query = $this->getDoctrine()->getConnection()->prepare($sql);
        $query->execute();

        $rows = $query->fetchAll();

        $data = [];
        foreach ($rows as $row)
//            $data[] = [ substr($row['dt'], 0, 10), floatval($row['value']) ];
        //$data[] = [ substr($row['dt'], 0, 10), floatval($row['value']), floatval($row['value']) * 0.8, floatval($row['value']) * 1.2, floatval($row['value']), /*floatval($row['value']) * 1000*/ 100000 ];
//        $data[] = [ substr($row['dt'], 0, 10), floatval($row['value']), 1000, 1000, 1000, 100000 ];
        //$data[] = [ substr($row['dt'], 0, 10), 1000, 1000, 1000, floatval($row['value']), 100000 ];
        $data[] = [ substr($row['dt'], 0, 10), floatval($row['value']), rand(0, 10) ];

        return $this->render('charts/price.html.twig', [
            'params' => $params,
            'data' => $data,
        ]);

//var_dump($data);
//die();

//        $response = new JsonResponse(json_encode($data));

//        return $response;


//var_dump($data);
//die();


    }


}
