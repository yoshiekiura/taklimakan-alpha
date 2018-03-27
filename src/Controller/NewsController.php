<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;


use App\Entity\News;
//use Symfony\Component\Form\AbstractType;
//use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsController extends Controller
{
    /**
     * @Route("/news", name="news")
     */
    public function index()
    {

        $newsRepo = $this->getDoctrine()->getRepository(News::class);
        $news = $newsRepo->findAll();
//var_dump($news);
//die();
        // $greeting = $generator->getRandomGreeting();
        // $logger->info("Saying $greeting to $name!");

        //        $logger->info("Saying hello to $name!");
        //		return new Response("Hello $name!");

        return $this->render('news/index.html.twig', [
            //'controller_name' => 'NewsController',
            'news' => $news,
        ]);
    }
}
