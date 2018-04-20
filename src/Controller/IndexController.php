<?php

namespace App\Controller;

use App\Entity\News;
use App\Entity\Likes;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Cookie;

use Symfony\Component\Form\Extension\Core\Type\TextType;
//use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IndexController extends Controller
{
    /**
    * @Route("/", name="home")
    */

	public function index(LoggerInterface $logger, Request $request) {

        // Do we have to show Welcome Popup ?
        $showWelcome = $request->cookies->get('show-welcome') == 'false' ? false : true;

        $newsRepo = $this->getDoctrine()->getRepository(News::class);
        $likesRepo = $this->getDoctrine()->getRepository(Likes::class);

        // $news = $newsRepo->findAll();

        // Top 3 News
//        $news = $newsRepo->findBy([], ['id' => 'DESC'], 3);
        $news = $newsRepo->getNews(['limit' => 3]);
//        $comments = $commentsRepo->findAllBy(['content_type' => 'news', 'content_id'] );
//var_dump($news);
//die();
        // $likesRepo->like("news", 1, 66);
        // $likesRepo->dislike("news", 1, 66);
        // $likes = $likesRepo->getLikes("news", 1);

/*
        return $this->render('news/index.html.twig', [
            'news' => $news,
        ]);
*/



// just setup a fresh $task object (remove the dummy data)
//    $task = new Task();

    $form = $this->createFormBuilder(/*$task*/)
        ->add('email', TextType::class)
//        ->add('dueDate', DateType::class)
        ->add('subscribe', SubmitType::class, [ 'label' => 'Subscribe'])
        //->add('task', TextType::class)
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // $form->getData() holds the submitted values
        // but, the original `$task` variable has also been updated
        //$task = $form->getData();

        die("FORM-SUBMITTED");

        // ... perform some action, such as saving the task to the database
        // for example, if Task is a Doctrine entity, save it!
        // $entityManager = $this->getDoctrine()->getManager();
        // $entityManager->persist($task);
        // $entityManager->flush();

        return $this->redirectToRoute('task_success');
    }



        return $this->render('home/home.html.twig', [
            'menu' => 'home',
            'show_welcome' => $showWelcome,
            'news' => $news,
            'form' => $form->createView(),
        ]);

	}

    // Subscribing for the Taklimakan News Form

    public function subscribe(Request $request)
    {
        // creates a task and gives it some dummy data for this example
        //$task = new Task();
        //$task->setTask('Write a blog post');
        //$task->setDueDate(new \DateTime('tomorrow'));

//        $form = $this->createFormBuilder()
//            ->add('task', TextType::class)
//            ->add('dueDate', DateType::class)
//            ->add('save', SubmitType::class, array('label' => 'Create Task'))
//            ->getForm();

//        return $this->render('/forms/subscribe.html.twig', [ 'form' => $form->createView() ]);
    }

/*
    // @Route("/blog/{page}", name="blog_list", requirements={"page"="\d+"})

    / **
    * @Route("/api/hello/{namer}", name="api_hello")
    *
    * /
    public function apiHello($namer)
    {

        throw new \Exception('Something went wrong!');

        throw $this->createNotFoundException('The product does not exist');

        $url = $this->generateUrl(
            "api_hello",
            [
            'namer' => $namer
            ]
        );

        return $this->json([
            'url' => $url,
            'symfony' => 'rocks',
        ]);


    }
*/

}
