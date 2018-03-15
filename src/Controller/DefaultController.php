<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Psr\Log\LoggerInterface;
use App\GreetingGenerator;
//use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
    * @Route("/hello/{name}")
    */

	public function index($name, LoggerInterface $logger, GreetingGenerator $generator, Request $request) {

        $greeting = $generator->getRandomGreeting();
        $logger->info("Saying $greeting to $name!");



//        $logger->info("Saying hello to $name!");
//		return new Response("Hello $name!");
        return $this->render('default/index.html.twig', [
            'name' => $name,
        ]);

	}

    // @Route("/blog/{page}", name="blog_list", requirements={"page"="\d+"})

    /**
    * @Route("/api/hello/{namer}", name="api_hello")
    * 
    */
    public function apiHello($namer)
    {

        throw new \Exception('Something went wrong!');

        throw $this->createNotFoundException('The product does not exist');

        $url = $this->generateUrl(
            "api_hello",
            [
//            'slug' => 'it-works' , 
//            'slug' => 'it-works' , 
            /*'namer' => "why-do-you-need-the-name?" */
            'namer' => $namer
            ]
        );

  //      $url2 = $this->router->generate('blog', array(
    //        'page' => 2,
      //      'category' => 'Symfony',
//        ));

//        $abs_url = $this->generateUrl('api_hello', array('slug' => 'my-blog-post', 'name' => $name), UrlGeneratorInterface::ABSOLUTE_URL);


        return $this->json([
            //'name' => $name,
            'url' => $url,
//            'url2' => $url2,
//            'abs_url' => $abs_url,
            'symfony' => 'rocks',
        ]);


    }

    /** @Route("/simplicity") */

    public function simple()
    {
        return new Response('Simple! Easy! Great!');
    }


}

