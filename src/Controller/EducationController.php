<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Lecture;
use App\Entity\Course;
// use App\Entity\Joiner;

use App\Repository\LectureRepository;
use App\Repository\CourseRepository;
// use App\Repository\JoinerRepository;

class EducationController extends Controller
{
/*
    / **
     * @Route("/{url}", name="remove_trailing_slash", requirements={"url" = ".*\/$"})
     * /
    public function removeTrailingSlash(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        // 308 (Permanent Redirect) is similar to 301 (Moved Permanently) except
        // that it does not allow changing the request method (e.g. from POST to GET)
        return $this->redirect($url, 308);
    }
*/
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
    public function courses(Request $request)
    {
        // Do we have to show Welcome Popup ?
        $showWelcome = $request->cookies->get('show-welcome') == 'false' ? false : true;

        $filter = [];
        $courseRepo = $this->getDoctrine()->getRepository(Course::class);
        $courses = $courseRepo->getCourses($filter);

        return $this->render('edu/courses.html.twig', [
            'menu' => 'edu',
            'show_welcome' => $showWelcome,
            'courses' => $courses
        ]);
    }

    /**
     * @Route("/courses/{id}", name="courses_id", requirements={"id"="\d+"})
     * @Route("/courses/{id}/", name="courses_id_trail", requirements={"id"="\d+"})
     * @Route("/courses/{id}/{translit}", name="courses_id_translit", requirements={"id"="\d+"})
     */
    public function show($id, $translit = '', Request $request)
    {
        // Do we have to show Welcome Popup ?
        $showWelcome = $request->cookies->get('show-welcome') == 'false' ? false : true;

        $courseRepo = $this->getDoctrine()->getRepository(Course::class);
        $course = $courseRepo->findOneBy([ 'id' => $id ]);

        if (!$course)
            throw $this->createNotFoundException('Sorry, this course does not exist!');

        $tags = array_map('trim', explode(',', $course->getTags()));

        // NB! Get Course' lectures via Joiner - rewrite it into Repository method later

//        $joinerRepo = $this->getDoctrine()->getRepository(Joiner::class);
//        $joiners = $joinerRepo->findBy([ 'fromType' => 'course', 'toType' => 'lecture', 'fromId' => $id ]);

        //$tags = array_map('trim', explode(',', $course->getTags()));
//var_dump(count($joiners)); die();
        // $lectureRepo = $this->getDoctrine()->getRepository(Lecture::class);
//        $lectures = $lectureRepo->findBy([ 'id' => $joiners ]);
        $lectures = $course->getLectures();

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
die("KEK");
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
    public function showLecture($id, $translit = '', Request $request)
    {
        // Do we have to show Welcome Popup ?
        $showWelcome = $request->cookies->get('show-welcome') == 'false' ? false : true;

        $lectureRepo = $this->getDoctrine()->getRepository(Lecture::class);
        $lecture = $lectureRepo->findOneBy([ 'id' => $id ]);

        if (!$lecture)
            throw $this->createNotFoundException('Sorry, this lecture does not exist!');

        $tags = array_map('trim', explode(',', $lecture->getTags()));
//echo $lecture->getId(); die();
        $courseRepo = $this->getDoctrine()->getRepository(Course::class);
        $course = $courseRepo->findOneBy([ 'id' => $lecture->getCourse() ]);

        $lectures = $course->getLectures();


//        $joinerRepo = $this->getDoctrine()->getRepository(Joiner::class);
//        $joiners = $joinerRepo->findBy([ 'fromType' => 'course', 'toType' => 'lecture', 'fromId' => $id ]);
        //$tags = array_map('trim', explode(',', $course->getTags()));
//var_dump(count($joiners)); die();
//        $lectureRepo = $this->getDoctrine()->getRepository(Lecture::class);
//        $lectures = $lectureRepo->findBy([ 'id' => $joiners ]);
//var_dump(count($lectures)); die();

        return $this->render('lectures/show.html.twig', [
            'menu' => 'edu',
            'show_welcome' => $showWelcome,
            'course' => $course, // ???
            'lecture' => $lecture,
            'lectures' => $lectures,
            'tags' => $tags, // ???
        ]);
    }


}
