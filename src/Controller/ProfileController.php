<?php

namespace App\Controller;

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
        ]);
    }
}
