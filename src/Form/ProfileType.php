<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', TextType::class, [
                'label' => 'First name',
                'required' => true,
                'constraints' => [
                    new Assert\NotNull(),
                ],
            ])
            ->add('last_name', TextType::class, [
                'label' => 'Last name',
                'required' => true,
                'constraints' => [
                    new Assert\NotNull(),
                ],
            ])
            ->add('erc20_token', TextType::class, [
                'label' => 'ERC-20 Wallet',
                'required' => false,
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^(0x)?[0-9a-zA-Z]{40}$/',
                    ]),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'required' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'first_options'  => [
                    'label' => 'Password'
                ],
                'second_options' => [
                    'label' => 'Repeat Password'
                ],
                'constraints' => [
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
                ],
            ])
        ;
    }
}
