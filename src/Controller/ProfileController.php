<?php

namespace App\Controller;

use App\Entity\Journal;
use App\Entity\User;
use App\Form\ProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

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
                'limit' => 3,
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
        /** @var User $user */
        $user = $this->getUser();

        $session = $request->getSession();
        $session->remove('profile_data');

        $form = $this->createForm(ProfileType::class, [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'erc20_token' => $user->getErc20Token(),
        ]);
        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $changedData = [];
                $data = $form->getData();
                if ($data['first_name'] !== $user->getFirstName()) {
                    $changedData['first_name'] = $data['first_name'];
                }
                if ($data['last_name'] !== $user->getLastName()) {
                    $changedData['last_name'] = $data['last_name'];
                }
                if ($data['erc20_token'] !== $user->getErc20Token()) {
                    $changedData['erc20_token'] = $data['erc20_token'];
                }
                if ($data['password']) {
                    $changedData['password'] = $data['password'];
                }

                if ($changedData) {
                    $session->set('profile_data', $changedData);
                    return $this->redirectToRoute('app_profile_confirm');
                }
                return $this->redirectToRoute('app_profile_edit');
            }
        }

        return $this->render('profile/edit.html.twig', [
            'hide_footer' => true,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/confirm")
     * @Method(methods={"GET", "POST"})
     */
    public function confirmAction(Request $request)
    {
        $session = $request->getSession();
        if (!$session->has('profile_data')) {
            throw $this->createAccessDeniedException();
        }
        $data = $session->get('profile_data');

        $fb = $this->createFormBuilder();
        $fb->add('password', PasswordType::class, [
            'label' => 'Current password',
            'required' => true,
            'constraints' => [
                new Assert\NotNull(),
            ],
        ]);
        $form = $fb->getForm();

        /** @var User $user */
        $user = $this->getUser();

        $invalidPassword = false;
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $password = $form->get('password')->getData();
                $encoder = $this->get('security.password_encoder');
                if ($encoder->isPasswordValid($user, $password)) {
                    if (array_key_exists('first_name', $data)) {
                        $user->setFirstName($data['first_name']);
                    }
                    if (array_key_exists('last_name', $data)) {
                        $user->setLastName($data['last_name']);
                    }
                    if (array_key_exists('erc20_token', $data)) {
                        $user->setErc20Token($data['erc20_token']);
                    }
                    if (array_key_exists('password', $data)) {
                        $user->setPassword($encoder->encodePassword($user, $data['password']));
                    }

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();

                    $this->get('journal')->log($user, Journal::ACTION_CHANGE_USER_DATA, $data);

                    $session->remove('profile_data');

                    $message = (new \Swift_Message('Profile has been changed'))
                        ->setFrom($this->getParameter('sender_email'))
                        ->setTo($user->getEmail())
                        ->setBody(
                            $this->renderView(
                                'emails/changed_profile.html.twig',
                                $data
                            ),
                            'text/html'
                        )
                    ;

                    $this->get('mailer')->send($message);

                    return $this->redirectToRoute('app_profile_edit');
                }
                $invalidPassword = true;
            }
        }

        return $this->render('profile/confirm.html.twig', [
            'hide_footer' => true,
            'form' => $form->createView(),
            'invalidPassword' => $invalidPassword,
        ]);
    }
}
