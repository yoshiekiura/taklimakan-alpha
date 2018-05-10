<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/profile")
 */
class ProfileController extends Controller
{
    /**
     * @Route("")
     * @Method(methods={"GET"})
     */
    public function viewAction()
    {
        return $this->render('profile/view.html.twig', [
            'hide_footer' => true,
            'news_list' => $this->getDoctrine()->getRepository('App:News')->getNews([
                'limit' => 4,
            ]),
            'courses' => $this->getDoctrine()->getRepository('App:Course')->getCourses([
                'limit' => 4,
            ]),
        ]);
    }

    /**
     * @Route("/edit")
     * @Route(methods={"GET", "POST"})
     */
    public function editAction(Request $request)
    {
        return $this->render('profile/edit.html.twig');
    }
}
