<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\News;
use App\Entity\Tags;
//use Symfony\Component\Form\AbstractType;
//use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsController extends Controller
{
    /**
     * @Route("/news", name="news")
     */
    public function index(Request $request)
    {
        $tagsFilter = explode(',', $request->query->get('tags'));

        $newsRepo = $this->getDoctrine()->getRepository(News::class);
        if (count($tagsFilter))
            $news = $newsRepo->getNewsByFilter($tagsFilter);        
        else
            $news = $newsRepo->findAll();

        $tagsRepo = $this->getDoctrine()->getRepository(Tags::class);
        $tags = $tagsRepo->findAll();

//var_dump($tags);
//die();
        // $greeting = $generator->getRandomGreeting();
        // $logger->info("Saying $greeting to $name!");

        //        $logger->info("Saying hello to $name!");
        //		return new Response("Hello $name!");

        return $this->render('news/index.html.twig', [
            //'controller_name' => 'NewsController',
            'news' => $news,
            'tags' => $tags,
        ]);
    }
}
