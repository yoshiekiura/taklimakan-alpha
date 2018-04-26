<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class SecurityController extends Controller
{
    /**
     * @Route("/register")
     * @Method(methods={"POST"})
     */
    public function registerAction(Request $request)
    {
        $form = $this->createForm(RegistrationType::class);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $this->get('doctrine')->getRepository('App:User')->findOneBy(['email' => $email]);
            if (!$user) {
                try {
                    $code = random_int(100000, 999999);
                } catch (\Exception $e) {
                    $code = mt_rand(100000, 999999);
                }

                $message = (new \Swift_Message('Hello Email'))
                    ->setFrom('noreply@taklimakan.network')
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                            'emails/confirm-email.html.twig',
                            ['code' => $code]
                        ),
                        'text/html'
                    )
                ;

                $this->get('mailer')->send($message);

                $data = $form->getData();
                $data['code'] = $code;

                $request->getSession()->set('registration_data', serialize($data));

                return $this->json([
                    'success' => true,
                    // 'code' => $code,
                ]);
            }
            return $this->json([
                'success' => false,
                'exists_email' => true,
            ]);
        }

        return $this->json([
            'success' => false,
        ]);
    }

    /**
     * @Route("/confirm-code")
     * @Method(methods={"POST"})
     */
    public function confirmCodeAction(Request $request)
    {
        $data = $request->getSession()->get('registration_data', null);
        if (is_null($data)) {
            return $this->json([
                'success' => false,
            ]);
        }
        $data = unserialize($data);
        if ($data['code'] !== $request->request->getInt('code', 0)) {
            return $this->json([
                'success' => false,
            ]);
        }

        $password = $this->get('security.password_encoder')->encodePassword(($user = new User()), $data['password']);

        $user
            ->setEmail($data['email'])
            ->setRole(User::ROLE_INVESTOR)
            ->setErc20Token($data['erc20_token'])
            ->setFirstName($data['first_name'])
            ->setLastName($data['last_name'])
            ->setPassword($password)
        ;

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $request->getSession()->remove('registration_data');

        $request->getSession()->invalidate();
        $this->get('security.token_storage')->setToken(null);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);

        return $this->json([
            'success' => true,
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getDoctrine()->getRepository('App:User')
            ->findOneBy(['email' => $request->request->get('_username')]);

        if (!$user) {
            return $this->json([
                'success' => false,
            ]);
        }

        $isPasswordValid = $this->get('security.password_encoder')
            ->isPasswordValid($user, $request->request->get('_password'));

        if (!$isPasswordValid) {
            return $this->json([
                'success' => false,
            ]);
        }

        $request->getSession()->invalidate();
        $this->get('security.token_storage')->setToken(null);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);

        return $this->json([
            'success' => true,
        ]);
    }

    /**
     * @Route("/forgot-password")
     * @Method(methods={"POST"})
     */
    public function forgotPasswordAction(Request $request)
    {
        $email = $request->request->get('email');
        if (is_null($email) || !is_string($email) || strlen(trim($email)) < 6) {
            return $this->json([
                'success' => false,
            ]);
        }

        $doctrine = $this->getDoctrine();
        $user = $doctrine->getRepository('App:User')
            ->findOneBy(['email' => $request->request->get('email')]);

        if (!$user) {
            return $this->json([
                'success' => false,
            ]);
        }

        try {
            $code = random_int(100000, 999999);
        } catch (\Exception $e) {
            $code = mt_rand(100000, 999999);
        }

        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('noreply@taklimakan.network')
            ->setTo($email)
            ->setBody(
                $this->renderView(
                    'emails/confirm-email.html.twig',
                    ['code' => $code]
                ),
                'text/html'
            )
        ;

        $this->get('mailer')->send($message);

        $em = $doctrine->getManager();
        $user->setConfirmationCode($code);
        $em->persist($user);
        $em->flush();

        $request->getSession()->set('restored_email', $email);

        return $this->json([
            'success' => true,
        ]);
    }

    /**
     * @Route("/confirmRestoreCode")
     * @Method(methods={"POST"})
     */
    public function confirmRestoreCodeAction(Request $request)
    {
        $email = $request->getSession()->get('restored_email');
        if (!$email) {
            return $this->json([
                'success' => false,
            ]);
        }

        $code = (string)$request->request->get('code');

        $doctrine = $this->getDoctrine();
        $user = $doctrine->getRepository('App:User')->findOneBy([
            'email' => $email,
            'confirmationCode' => $code,
        ]);

        if (!$user) {
            return $this->json([
                'success' => false,
            ]);
        }

        $user->setConfirmationCode(null);

        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json([
            'success' => true,
        ]);
    }

    /**
     * @Route("/change-password")
     * @Method(methods={"POST"})
     */
    public function changePasswordAction(Request $request)
    {
        $email = $request->getSession()->get('restored_email');
        if (!$email) {
            return $this->json([
                'success' => false,
            ]);
        }

        $password = $request->request->get('password_first');
        $repeatedPassword = $request->request->get('password_second');

        $validator = Validation::createValidator();
        $violations = $validator->validate($password, [
            new Assert\NotNull(),
            new Assert\NotNull(),
            new Assert\Length([
                'min' => 8,
            ]),
            new Assert\Regex([
                'pattern' => '/^[0-9a-zA-Z$&+,:;=?@#|\'<>.-^*()%!]*$/',
            ]),
            new Assert\Regex([
                'pattern' => '/[a-zA-Z]+/',
            ]),
            new Assert\Regex([
                'pattern' => '/[0-9]+/',
            ]),
        ]);

        if (count($violations) !== 0 || $password !== $repeatedPassword) {
            return $this->json([
                'success' => false,
            ]);
        }

        $doctrine = $this->getDoctrine();
        $user = $doctrine->getRepository('App:User')->findOneBy([
            'email' => $email,
        ]);

        if (!$user || $user->getConfirmationCode() !== null) {
            return $this->json([
                'success' => false,
            ]);
        }

        $encoder = $this->get('security.password_encoder');

        if ($encoder->isPasswordValid($user, $password)) {
            return $this->json([
                'success' => false,
                'equal_previous_password' => true,
            ]);
        }

        $user->setPassword($encoder->encodePassword($user, $password));

        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();

        $request->getSession()->remove('restored_email');
        $request->getSession()->invalidate();
        $this->get('security.token_storage')->setToken(null);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);

        return $this->json([
            'success' => true,
        ]);
    }
}
