<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\MakerBundle\Validator;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class LabController extends Controller
{

    /**
     * @Route("/lab", name="lab")
     * @Route("/labs", name="labs")
     * @Route("/lab/", name="lab_trail")
     * @Route("/labs/", name="labs_trail")
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

        // --------------------------------------------------------

        return $this->render('charts/lab.html.twig', [
            'menu' => '', // 'charts',
            'params' => [], // $params,
            'show_welcome' => false,
            'allowed' => $allowed,
            'types' => [], // $types,
            'pair' => $pair,
            'data' => [], // $data,
            'volume' => [], // $volume,
            'use_anycharts' => true,
        ]);

    }

    // NB! Input JSON should be stored within BODY and application/json HEADER is set up

    /**
     * @Route("api/lab", name="api_lab")
     */
    public function api(Request $request)
    {

        if (!$request->isXMLHttpRequest())
            throw new BadRequestHttpException("[ERR] Only AJAX requests are allowed!");

        $content = $request->getContent();
        // if(empty($content))
        //    throw new BadRequestHttpException("[ERR] JSON is empty!");
        // if(!Validator::isValidJsonString($content))
        //    throw new BadRequestHttpException("[ERR] Content is not a valid JSON!");
        // $params = new ArrayCollection(json_decode($content, true));

        $params = json_decode($content, true);
        $pair = $params['pair'];
        $type = $params['type'] > 0 ? intval($params['type']) : 1;

        $conn = $this->getDoctrine()->getConnection();

        // What currency pairs is allowed to show and data already stored in DB?

        $sql = 'SELECT data FROM pair_set WHERE id = "1"';
        $query = $conn->prepare($sql);
        $query->execute();
        $json = $query->fetchColumn();
        $allowed = json_decode($json);

        if (!in_array($pair, $allowed))
            $pair = "BTC-USD";

        // Special case for type #11
        if ($type == 11)
            $pair = "INDEX001";

        $sql = 'SELECT dt, value FROM numerical_analytics WHERE type_id = "' . $type . '" AND pair = "' . $pair . '"';
        $query = $this->getDoctrine()->getConnection()->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();

        $data = [];
        foreach ($rows as $row)
            $data[] = [ substr($row['dt'], 0, 10), floatval($row['value']) ];

        $response = new JsonResponse($data);

        return $response;

    }

}
