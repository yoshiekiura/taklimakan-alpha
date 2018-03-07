<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use App\GreetingGenerator;

class DefaultController extends AbstractController
{
    /**
    * @Route("/hello/{name}")
    */

	public function index($name, LoggerInterface $logger, GreetingGenerator $generator) {

        $greeting = $generator->getRandomGreeting();
        $logger->info("Saying $greeting to $name!");

//        $logger->info("Saying hello to $name!");
//		return new Response("Hello $name!");
        return $this->render('default/index.html.twig', [
            'name' => $name,
        ]);

	}


    /**
    * @Route("/api/hello/{name}")
    */
    public function apiExample($name)
    {
        return $this->json([
            'name' => $name,
            'symfony' => 'rocks',
        ]);
    }

    /** @Route("/simplicity") */

    public function simple()
    {
        return new Response('Simple! Easy! Great!');
    }
}

