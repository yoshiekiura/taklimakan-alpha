<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\MakerBundle\Validator;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class TradingViewController extends Controller
{

    /**
     * @Route("/tradingview", name="tv")
     */
    public function index(Request $request)
    {
        return $this->render('charts/tradingview.html.twig', [
            'menu' => '', // 'charts',
            'params' => [], // $params,
            'show_welcome' => false,
            'types' => [], // $types,
        ]);

    }

}
