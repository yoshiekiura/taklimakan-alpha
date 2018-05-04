<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\MakerBundle\Validator;

//use Symfony\Component\Form\AbstractType;
//use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\OptionsResolver\OptionsResolver;

// use Symfony\Component\HttpFoundation\Cookie;

class LabController extends Controller
{

    /**
     * @Route("/lab", name="lab")
     * @Route("/lab/", name="lab_trail")
     */
    public function index(Request $request)
    {

        $conn = $this->getDoctrine()->getConnection();

        // What currency pairs is allowed to show and data already stored in DB?

        $sql = 'SELECT data FROM pair_set WHERE id = "1"';
        $query = $conn->prepare($sql);
        $query->execute();
        $json = $query->fetchColumn();
        $allowed = json_decode($json);

        $pair = $request->query->get('pair');

/*
        if (!in_array($pair, $allowed))
            $pair = "BTC-USD";

        $params['pair'] = $pair;

        // --- Price 1 -------------------------------------------------

        $sql = 'SELECT * FROM numerical_analytics WHERE type_id = "1" AND pair = "' . $pair . '"';
        $query = $conn->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();

        foreach ($rows as $row)
            $data[] = [ substr($row['dt'], 0, 10), floatval($row['value']) ];

        // --- Volume 2 -------------------------------------------------

        $sql = 'SELECT * from numerical_analytics where type_id="2" and pair="' . $pair . '"';
        $query = $conn->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();

        foreach ($rows as $row)
            $volume[] = [ substr($row['dt'], 0, 10), floatval($row['value']) ];
*/

        // --------------------------------------------------------

        return $this->render('charts/lab.html.twig', [
            'menu' => '', // 'charts',
            'params' => [], // $params,
            'show_welcome' => false,
            'allowed' => $allowed,
            'pair' => $pair,
            'data' => [], // $data,
            'volume' => [], // $volume,
/*            'volatility' => $volatility,
            'alpha' => $alpha,
            'beta' => $beta,
            'sharpe' => $sharpe,
            'crypto_index' => $crypto_index, */
            'show_charts' => true,
        ]);

    }

    /* @ Route("api/charts/{type}", name="api_charts")
    public function getData($type, Request $request) */

    // NB! Input JSON should be stored within BODY and application/json HEADER is set up

    /**
     * @Route("api/lab", name="api_lab")
     */
    public function api(Request $request)
    {

        $content = $request->getContent();
        if(empty($content))
            throw new BadRequestHttpException("[ERR] JSON is empty!");
        // if(!Validator::isValidJsonString($content))
        //    throw new BadRequestHttpException("[ERR] Content is not a valid JSON!");
        // $params = new ArrayCollection(json_decode($content, true));

        $params = json_decode($content, true);
        $pair = $params['pair'];

        $conn = $this->getDoctrine()->getConnection();

        // What currency pairs is allowed to show and data already stored in DB?

        $sql = 'SELECT data FROM pair_set WHERE id = "1"';
        $query = $conn->prepare($sql);
        $query->execute();
        $json = $query->fetchColumn();
        $allowed = json_decode($json);

        if (!in_array($pair, $allowed))
            $pair = "BTC-USD";

        $sql = 'SELECT dt, value FROM numerical_analytics WHERE type_id = "1" AND pair = "' . $pair . '" LIMIT 10';
        $query = $this->getDoctrine()->getConnection()->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();

        $data = [];
        foreach ($rows as $row)
            $data[] = [ $row['dt'], $row['value'] ];

//var_dump($rows);
//die();

        $response = new JsonResponse(json_encode($data));

        return $response;

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

}
