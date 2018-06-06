<?php

namespace App\Controller;

use App\Entity\News;
use App\Entity\Course;
use App\Entity\Lecture;
use App\Entity\Likes;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Cookie;

//use Symfony\Component\Form\Extension\Core\Type\TextType;
//use Symfony\Component\Form\Extension\Core\Type\DateType;
//use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Security\Core\User\UserInterface;

// NB! Have to dig into native caching oprions of Symfony and FOS HTTP Cache Bundle Later !

// http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/cache.html
// use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
//     * @Cache(maxage="11", smaxage="99")

class IndexController extends Controller
{
    /**
    * @Route("/", name="home")
    */
	public function index(LoggerInterface $logger, Request $request, UserInterface $user = null)
    {

        // Do we have to show Welcome Popup ?
        $showWelcome = $request->cookies->get('show-welcome') == 'false' ? false : true;

        $newsRepo = $this->getDoctrine()->getRepository(News::class);
        $likesRepo = $this->getDoctrine()->getRepository(Likes::class);

        // Top 3 News
        $news = $newsRepo->getNews(['limit' => 3]);

/*
    $tags = $request->query->get('tags') ? explode(',', $request->query->get('tags')) : [];
    if ($tags) $filter['tags'] = $tags;

    $page = $request->query->get('page') ? intval($request->query->get('page')) : 1;
    if ($page) $filter['page'] = $page - 1;

    $limit = $request->query->get('limit') ? intval($request->query->get('limit')) : 6;
    if ($limit) $filter['limit'] = $limit;

    $level = $request->query->get('level') ? intval($request->query->get('level')) : null;
    if ($level) $filter['level'] = $level;
*/

    // NB! We have to show total of 3 courses and lectures

    if ($user)
        $filter['user'] = $user->getId();

    $filter['limit'] = 3;
    $courseRepo = $this->getDoctrine()->getRepository(Course::class);
    $courses = $courseRepo->getCourses($filter);
    // foreach ($courses as &$course)
    //     $course['type'] = 'course';

    $lectureRepo = $this->getDoctrine()->getRepository(Lecture::class);
    $filter['course'] = null; // We interesten in standalone lectures aka Articles or Materials here
    $standaloneLectures = $lectureRepo->getLectures($filter);

    foreach ($standaloneLectures as $lecture)
        $courses[] = $lecture;

    // Sort by date and trim by limit
    usort($courses, "self::twoDates");
    $courses = array_slice($courses, 0, 3);

    //$tagsRepo = $this->getDoctrine()->getRepository(Tags::class);
    //$allTags = $tagsRepo->findAll();


    // Show only FULL courses on the Home page

/*
    $lectureRepo = $this->getDoctrine()->getRepository(Lecture::class);
    $filter['course'] = null;
    $standaloneLectures = $lectureRepo->getLectures($filter);

    foreach ($standaloneLectures as $lecture) {
    //    $lecture['type'] = 'lecture';
        $courses[] = $lecture;
    }

//    $tagsRepo = $this->getDoctrine()->getRepository(Tags::class);
//    $allTags = $tagsRepo->findAll();

    $courses = array_slice($courses, 0, $filter['limit']);
*/

// just setup a fresh $task object (remove the dummy data)
//    $task = new Task();
/*
    // $form = $this->createFormBuilder($task)
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
*/


        $response = $this->render('home/home.html.twig', [
            'menu' => 'home',
            'show_welcome' => $showWelcome,
            'news' => $news,
            'courses' => $courses,
//            'form' => $form->createView(),
        ]);

        // cache for 3600 seconds
        //$response->setMaxAge(33);
        //$response->setSharedMaxAge(66);

        // (optional) set a custom Cache-Control directive
        // $response->headers->addCacheControlDirective('must-revalidate', false);

/*
        $response->setCache(array(
            //'etag'          => ,
            //'last_modified' => $date,
            'max_age'       => 10,
            's_maxage'      => 10,
            'public'        => true,
            // 'private'    => true,
        ));
*/
        return $response;

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

    private static function twoDates($a, $b)
    {
        return $a["date"] < $b["date"];
    }


}
