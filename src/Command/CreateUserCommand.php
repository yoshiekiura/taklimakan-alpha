<?php

namespace App\Command;

use App\Entity\User;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateUserCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('user:create')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $doctrine = $this->getContainer()->get('doctrine');
        $repository = $doctrine->getRepository('App:User');

        do {
            $email = $helper->ask($input, $output, new Question('E-mail: '));
            if (is_null($email)) {
                $output->writeln('E-mail is required');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $output->writeln('E-mail is not valid');
            } elseif ($repository->findOneBy(['email' => $email])) {
                $output->writeln('User with this E-mail already exists');
                $email = null;
            }
        } while (is_null($email) || !filter_var($email, FILTER_VALIDATE_EMAIL));

        do {
            $password = $helper->ask($input, $output, new Question('Password: '));
            if (is_null($password)) {
                $output->writeln('Password is required');
            }
        } while (is_null($password));

        do {
            $role = $helper->ask($input, $output, new Question('Role: '));
            if (is_null($role)) {
                $output->writeln('Role is required');
            } elseif (!in_array($role, User::$roles)) {
                $output->writeln('Role is invalid');
            }
        } while (is_null($role) || !in_array($role, User::$roles));

        $user = new User();
        $encoder = $this->getContainer()->get('security.password_encoder');
        $password = $encoder->encodePassword(($user = new User()), $password);


        $user
            ->setEmail($email)
            ->setRole($role)
            ->setPassword($password)
        ;

        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();
    }
}
