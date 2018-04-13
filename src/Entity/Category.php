<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @ORM\Table(name="categories", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci"})
 */
class Category
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
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->getName();
    }

}
