<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Lecture;
use App\Entity\Course;
use App\Entity\Joiner;

use App\Repository\LectureRepository;
use App\Repository\CourseRepository;
use App\Repository\JoinerRepository;

class EducationController extends Controller
{
    /**
     * @Route("/edu", name="edu")
     */
    public function index(Request $request)
    {
        return $this->render('edu/index.html.twig', [
            'menu' => 'edu'
        ]);
    }

    /**
     * @Route("/courses", name="courses")
     */
    public function courses(Request $request)
    {
        // $courses = [1, 2, 3, 4, 5, 6, 7, 8, 9];

        $filter = [];
        $courseRepo = $this->getDoctrine()->getRepository(Course::class);
        $courses = $courseRepo->getCourses($filter);

        return $this->render('edu/courses.html.twig', [
            'menu' => 'edu',
            'courses' => $courses
        ]);
    }

    /**
     * @Route("/course/{id}/{translit}", name="course_show")
     */
    public function show($id, $translit, Request $request)
    {
        // Do we have to show Welcome Popup ?
        $showWelcome = $request->cookies->get('show-welcome') == 'false' ? false : true;

        $courseRepo = $this->getDoctrine()->getRepository(Course::class);
        $course = $courseRepo->findOneBy([ 'id' => $id ]);
        $tags = array_map('trim', explode(',', $course->getTags()));

        // NB! Get Course' lectures via Joiner - rewrite it into Repository method later

        $joinerRepo = $this->getDoctrine()->getRepository(Joiner::class);
        $joiners = $joinerRepo->findBy([ 'fromType' => 'course', 'toType' => 'lecture', 'fromId' => $id ]);
        //$tags = array_map('trim', explode(',', $course->getTags()));
//var_dump(count($joiners)); die();
        $lectureRepo = $this->getDoctrine()->getRepository(Lecture::class);
        $lectures = $lectureRepo->findBy([ 'id' => $joiners ]);
//var_dump(count($lectures)); die();
        return $this->render('courses/show.html.twig', [
            'menu' => 'edu',
            'show_welcome' => $showWelcome,
            'course' => $course,
            'lectures' => $lectures,
            'tags' => $tags,
        ]);
    }


}
