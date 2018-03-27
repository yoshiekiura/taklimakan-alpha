<?php

namespace App\Controller;

use App\Entity\News;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;

class IndexController extends Controller
{
    /**
    * @Route("/")
    */

	public function index(LoggerInterface $logger, Request $request) {

        $newsRepo = $this->getDoctrine()->getRepository(News::class);
        // $news = $newsRepo->findAll();

        // Top 3 News
        $news = $newsRepo->findBy([], ['id' => 'DESC'], 3);

        return $this->render('news/index.html.twig', [
            'news' => $news,
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
