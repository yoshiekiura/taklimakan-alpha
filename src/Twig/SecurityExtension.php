<?php

namespace App\Twig;

use App\Form\RegistrationType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Twig\Extension\AbstractExtension;

class SecurityExtension extends AbstractExtension
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var FormInterface */
    protected $registrationForm;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_Function('getRegistrationForm', [$this, 'getRegistrationForm'])
        ];
    }

    /**
     * @return \Symfony\Component\Form\FormView
     */
    public function getRegistrationForm()
    {
        if ($this->registrationForm === null) {
            $this->registrationForm = $this->formFactory->create(RegistrationType::class);
        }
        return $this->registrationForm->createView();
    }
}
