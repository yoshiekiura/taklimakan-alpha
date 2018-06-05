<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Lecture;
use App\Entity\Course;
use App\Entity\Tags;
use App\Entity\Likes;
use App\Entity\Rating;

//use App\Repository\LectureRepository;
//use App\Repository\CourseRepository;
// use App\Repository\JoinerRepository;

use Symfony\Component\Security\Core\User\UserInterface;

class EducationController extends Controller
{
    /**
     * @Route("/edu", name="edu")
     * @Route("/edu/", name="edu_trail")
     */
    public function index(Request $request)
    {
        return $this->render('edu/index.html.twig', [
            'menu' => 'edu'
        ]);
    }

    /**
     * @Route("/courses", name="courses")
     * @Route("/courses/", name="courses_trail")
     */
    public function courses(Request $request, UserInterface $user = null)
    {
        // Do we have to show Welcome Popup ?
        $showWelcome = $request->cookies->get('show-welcome') == 'false' ? false : true;

        $filter = [];

        $tags = $request->query->get('tags') ? explode(',', $request->query->get('tags')) : [];
        if ($tags) $filter['tags'] = $tags;

        $page = $request->query->get('page') ? intval($request->query->get('page')) : 1;
        if ($page) $filter['page'] = $page - 1;

        $limit = $request->query->get('limit') ? intval($request->query->get('limit')) : 6;
        // if ($limit) $filter['limit'] = $limit;
        // $filter['limit'] = 0; // We'll trim the list later

        $level = $request->query->get('level') ? intval($request->query->get('level')) : null;
        if ($level) $filter['level'] = $level;

        if ($user)
            $filter['user'] = $user->getId();

        $courseRepo = $this->getDoctrine()->getRepository(Course::class);
        $courses = $courseRepo->getCourses($filter);

        $lectureRepo = $this->getDoctrine()->getRepository(Lecture::class);
        $filter['course'] = null; // We interesten in standalone lectures aka Articles or Materials here
        $standaloneLectures = $lectureRepo->getLectures($filter);

//        foreach ($standaloneLectures as $lecture)
//            $courses[] = $lecture;

        // Sort by date and trim by limit
        usort($courses, "self::twoDates");
        $courses = array_slice($courses, ($page - 1) * $page, $limit);

        $tagsRepo = $this->getDoctrine()->getRepository(Tags::class);
        $allTags = $tagsRepo->findAll();

        return $this->render('edu/courses.html.twig', [
            'menu' => 'edu',
            'show_welcome' => $showWelcome,
            'courses' => $courses,
            'tags' => $allTags, // Selected tags to sort
            'filter' => [
                'sort' => null, // NB! Define sort orders later (new / older / trending / popular / etc)
                'level' => null,
                'tags' => implode($tags, ','),
            ],
            'paginator' => [
                'total' => null,   // Total items for paginator / NB! Do not count for now
                'page'  => $page,  // Current Page
                'limit' => $limit, // Max items on the page
            ],
            'page' => $page
        ]);
    }

    /**
     * @Route("/courses/{id}", name="courses_id", requirements={"id"="\d+"})
     * @Route("/courses/{id}/", name="courses_id_trail", requirements={"id"="\d+"})
     * @Route("/courses/{id}/{translit}", name="courses_id_translit", requirements={"id"="\d+"})
     */
    public function show($id, $translit = '', Request $request, UserInterface $user = null)
    {
        // Do we have to show Welcome Popup ?
        $showWelcome = $request->cookies->get('show-welcome') == 'false' ? false : true;

        $courseRepo = $this->getDoctrine()->getRepository(Course::class);
        $course = $courseRepo->findOneBy([ 'id' => $id ]);
        $course->type = 'course';

        if (!$course)
            throw $this->createNotFoundException('Sorry, this course does not exist!');

        $tags = array_map('trim', explode(',', $course->getTags()));
/*
        // NB! Is it better to use some sort of Helper Service here? Think about
        if ($user) {
            $type = 'course';
            $user_id = $user->getId();
            $likesRepo = $this->getDoctrine()->getRepository(Likes::class);
            $like = $likesRepo->findOneBy(['content_type' => $type, 'content_id' => $id, 'user_id' => $user_id]);
        }
        $course->like = isset($like) && $like ? 1 : 0;
*/
        $course->like = $this->getDoctrine()->getRepository(Likes::class)->getStatus('course', $id, $user);
        $course->rating = $this->getDoctrine()->getRepository(Rating::class)->getRating('course', $id);

        $lectures = $course->getActiveLectures();

        return $this->render('courses/show.html.twig', [
            'menu' => 'edu',
            'show_welcome' => $showWelcome,
            'course' => $course,
            'lectures' => $lectures,
            'tags' => $tags,
        ]);
    }

    // FIXME! There are no such a route on the website
    // Think twice - do we need it?

    /**
     * @Route("/lectures", name="lectures")
     * @Route("/lectures/", name="lectures_trail")
     */
    public function lectures(Request $request)
    {

        // https://stackoverflow.com/questions/10625491/symfony2-and-throwing-exception-error/35088605#35088605
        throw $this->createNotFoundException('This route does not exist!');

        // $filter = [];
        // $courseRepo = $this->getDoctrine()->getRepository(Course::class);
        // $courses = $courseRepo->getCourses($filter);

        // return $this->render('edu/courses.html.twig', [
        //     'menu' => 'edu',
        //     'courses' => $courses
        // ]);
    }

    /**
     * @Route("/lectures/{id}", name="lectures_id", requirements={"id"="\d+"})
     * @Route("/lectures/{id}/", name="lectures_id_trail", requirements={"id"="\d+"})
     * @Route("/lectures/{id}/{translit}", name="lectures_id_translit", requirements={"id"="\d+"})
     */
    public function showLecture($id, $translit = '', Request $request, UserInterface $user = null)
    {
        // Do we have to show Welcome Popup ?
        $showWelcome = $request->cookies->get('show-welcome') == 'false' ? false : true;

        $lectureRepo = $this->getDoctrine()->getRepository(Lecture::class);
        $lecture = $lectureRepo->findOneBy([ 'id' => $id ]);

        // FIXME! Rethink later - it looks like we have to remove TYPE from Lectures like we did it in Courses
        $lecture->setType('lecture');

        if (!$lecture)
            throw $this->createNotFoundException('Sorry, this lecture does not exist!');

        $tags = array_map('trim', explode(',', $lecture->getTags()));

        $lecture->like = $this->getDoctrine()->getRepository(Likes::class)->getStatus('lecture', $id, $user);
        $lecture->rating = $this->getDoctrine()->getRepository(Rating::class)->getRating('lecture', $id);

        // If there course and other lectures, get them all. Otherwise it's just a standalone lecture
        if ($lecture->getCourse()) {
            $courseRepo = $this->getDoctrine()->getRepository(Course::class);
            $course = $courseRepo->findOneBy([ 'id' => $lecture->getCourse() ]);
            $course->like = $this->getDoctrine()->getRepository(Likes::class)->getStatus('course', $course->getId(), $user);
            $course->rating = $this->getDoctrine()->getRepository(Rating::class)->getRating('course', $course->getId());
            $course->type="course"; // NB! Is it possible to retrurn virtual property on FindByOne or Course Entity ?
            $lectures = $course->getLectures();
        }
        else {
            $course = null;
            $lectures = null;
        }

        return $this->render('lectures/show.html.twig', [
            'menu' => 'edu',
            'show_welcome' => $showWelcome,
            'course' => $course,
            'lecture' => $lecture,
            'lectures' => $lectures,
            'tags' => $tags,
        ]);
    }

    // Callback Helper for AJAX News <Load More> Button

    /**
     * @Route("/courses/more", name="courses_more")
     */
    public function more(Request $request, UserInterface $user = null)
    {
        if (!$request->isXMLHttpRequest())
            throw new BadRequestHttpException("[ERR] Only AJAX requests are allowed!");

        $content = $request->getContent();
        $params = json_decode($content, true);
        $page = $params['page'] > 0 ? intval($params['page']) : 0;
        $tags = [];
        $level = count($params['level']) > 0 ? $params['level'] : 0;
        $limit = 6;

        $filter = [];
        $filter['page'] = $page;
        $filter['tags'] = []; // $tags;
        $filter['level'] = $level;

        if ($user)
            $filter['user'] = $user->getId();

        // We'll limit the list later, when combine courses and standalone lectures
        // $filter['limit'] = 0;

        $courseRepo = $this->getDoctrine()->getRepository(Course::class);
        $courses = $courseRepo->getCourses($filter);

        $lectureRepo = $this->getDoctrine()->getRepository(Lecture::class);
        $filter['course'] = null;
        $standaloneLectures = $lectureRepo->getLectures($filter);

        foreach ($standaloneLectures as $lecture)
            $courses[] = $lecture;

        // Sort by date And trim the list for default limit
        usort($courses, "self::twoDates");
        $courses = array_slice($courses, $page * $limit, $limit);

        if (count($courses))
            $template = $this->render('courses/more.html.twig', [ 'courses' => $courses, 'page' => $page ])->getContent();
        else
            $template = '';

        $response = new JsonResponse([ 'page' => $page, 'tags' => $tags, 'html' => $template ]);

        return $response;

    }

    private static function twoDates($a, $b)
    {
        return $a["date"] < $b["date"];
    }

}
