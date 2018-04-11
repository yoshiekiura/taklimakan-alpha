<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Lecture;
use App\Entity\Course;

use App\Repository\LectureRepository;
use App\Repository\CourseRepository;

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
        $courses = [1, 2, 3, 4, 5, 6, 7, 8, 9];

        return $this->render('edu/courses.html.twig', [
            'menu' => 'edu',
            'courses' => $courses
        ]);
    }

}
