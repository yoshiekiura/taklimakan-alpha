<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @Route("/api/charts/{type}", name="api_charts")
     */
    public function showChart($type, Request $request)
    {

//        $ChartsRepo = $this->getDoctrine()->getRepository(Charts::class);
//        $Charts = $ChartsRepo->findAll();
//var_dump($Charts);
//die();
        // $greeting = $generator->getRandomGreeting();
        // $logger->info("Saying $greeting to $name!");

        //        $logger->info("Saying hello to $name!");
        //		return new Response("Hello $name!");

var_dump($type);
die();

        return $this->render('charts/index.html.twig', [
            //'controller_name' => 'ChartsController',
            'charts' => $charts,
        ]);
    }

}
