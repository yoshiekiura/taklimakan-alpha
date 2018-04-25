<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/profile")
 */
class ProfileController extends Controller
{
    /**
     * @Route("")
     */
    public function indexAction()
    {
        /** @var UserInterface $user */
        $user = $this->getUser();
        return new Response('Hello, ' . $user->getUsername());
    }
}
