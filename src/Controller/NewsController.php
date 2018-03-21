<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NewsController extends Controller
{
    /**
     * @Route("/news", name="news")
     */
    public function index()
    {
        return $this->render('news/index.html.twig', [
            'controller_name' => 'NewsController',
        ]);
    }
}
