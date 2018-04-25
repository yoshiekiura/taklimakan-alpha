<?php

namespace App\Controller;

use function PHPSTORM_META\type;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/profile")
 */
class ProfileController extends Controller
{
    /**
     * @Route("")
     */
    public function viewAction()
    {
        return $this->render('profile/view.html.twig', [
            'hide_footer' => true,
            'last_news' => $this->getDoctrine()->getRepository('App:News')->getLastNews(3),
        ]);
    }
}
