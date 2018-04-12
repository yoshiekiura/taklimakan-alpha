<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProviderRepository")
 * @ORM\Table(name="providers")
 */
class Provider
{

    public function __construct() {
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    public function getId() {
        return $this->id;
    }

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $name;
    public function getName()
    {
        return $this->name;
    }
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $web;
    public function getWeb()
    {
        return $this->web;
    }
    public function setWeb($web)
    {
        $this->web = $web;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->getName();
    }

}
