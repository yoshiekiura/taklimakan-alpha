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

use Symfony\Component\Security\Core\User\UserInterface;

use App\Entity\Lecture;
use App\Entity\Course;
// use App\Entity\Joiner;
use App\Entity\Tags;
use App\Entity\Likes;


/**
 * @Route("/profile")
 */
class ProfileController extends Controller
{
    /**
     * @Route("")
     * @Method(methods={"GET"})
     */
    public function viewAction(Request $request, UserInterface $user = null)
    {

        $filter = [];
        $limit = 4;
        $page = 0;

        $news = $this->getDoctrine()->getRepository('App:News')->getNews(['limit' => 3]);

/*
        $tags = $request->query->get('tags') ? explode(',', $request->query->get('tags')) : [];
        if ($tags) $filter['tags'] = $tags;

        $page = $request->query->get('page') ? intval($request->query->get('page')) : 1;
        if ($page) $filter['page'] = $page - 1;

        $limit = $request->query->get('limit') ? intval($request->query->get('limit')) : 6;
        // if ($limit) $filter['limit'] = $limit;
        // $filter['limit'] = 0; // We'll trim the list later

        $level = $request->query->get('level') ? intval($request->query->get('level')) : null;
        if ($level) $filter['level'] = $level;
*/
        if ($user)
            $filter['user'] = $user->getId();

        $courseRepo = $this->getDoctrine()->getRepository(Course::class);
        $courses = $courseRepo->getCourses($filter);

        $lectureRepo = $this->getDoctrine()->getRepository(Lecture::class);
        $filter['course'] = null; // We interested in standalone lectures aka Articles or Materials here
        $standaloneLectures = $lectureRepo->getLectures($filter);

        foreach ($standaloneLectures as $lecture)
            $courses[] = $lecture;

        // Sort by date and trim by limit
        usort($courses, "self::twoDates");
        usort($courses, "self::isFavorite");
        $courses = array_slice($courses, ($page - 1) * $page, $limit);

        //$tagsRepo = $this->getDoctrine()->getRepository(Tags::class);
        //$allTags = $tagsRepo->findAll();

        return $this->render('profile/view.html.twig', [
            'hide_footer' => true,
            'news_list' => $news,
            'courses' => $courses
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

                    if (array_key_exists('password', $data)) {
                        $data['password'] = '!UNAVAILABLE!';
                    }
                    $this->get('journal')->log($user, Journal::ACTION_CHANGE_USER_DATA, $data);

                    $session->remove('profile_data');

                    $fields = array_keys($data);
                    if (($index = array_search('first_name', $fields)) !== false) {
                        $fields[$index] = 'First name';
                    }
                    if (($index = array_search('last_name', $fields)) !== false) {
                        $fields[$index] = 'Last name';
                    }
                    if (($index = array_search('erc20_token', $fields)) !== false) {
                        $fields[$index] = 'Wallet';
                    }
                    if (($index = array_search('password', $fields)) !== false) {
                        $fields[$index] = 'Password';
                    }

                    $message = (new \Swift_Message('Profile has been changed'))
                        ->setFrom($this->getParameter('sender_email'))
                        ->setTo($user->getEmail())
                        ->setBody(
                            $this->renderView(
                                'emails/changed-profile.html.twig',
                                [
                                    'fields' => $fields,
                                ]
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

    private static function twoDates($a, $b)
    {
        return $a["date"] < $b["date"];
    }

    private static function isFavorite($a, $b)
    {
        return $a["like"] < $b["like"];
    }

}
