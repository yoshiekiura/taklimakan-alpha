<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JoinerRepository")
 * @ORM\Table(name="joiner", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci"})
 */

class Joiner
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

    // Item Number in Collection (for example, Lecture #3 in some Bla-Bla Course)

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $number;
    public function getNumber()
    {
        return $this->number;
    }
    public function setNumber($number)
    {
        $this->number = $number;
    }
/*
    / **
     * @ORM\Column(type="string", length=255, nullable=true)
     * /
    private $title;
    public function getTitle()
    {
        return $this->title;
    }
    public function setTitle($title)
    {
        $this->title = $title;
    }
*/

    // Content Type for the left part of Join (for example = "course")

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $fromType;
    public function getFromType()
    {
        if (!$this->fromType)
            $this->fromType = 'course';
        return $this->fromType;
    }
    public function setFromType($fromType)
    {
        $this->fromType = $fromType;
    }

    // Content Type for the right part of Join (for example = "lecture")

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $toType;
    public function getToType()
    {
        if (!$this->toType)
            $this->toType = 'lecture';
        return $this->toType;
    }
    public function setToType($toType)
    {
        $this->toType = $toType;
    }

    // Content ID for the left part of Join (for example Course #2)

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $fromId;
    public function getFromId()
    {
        return $this->fromId;
    }
    public function setFromId($fromId)
    {
        $this->fromId = $fromId;
    }

    // Content ID for the right part of Join (for example lecture #23)

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $toId;
    public function getToId()
    {
        return $this->toId;
    }
    public function setToId($toId)
    {
        $this->toId = $toId;
    }
/*
    / **
     * @ORM\Column(type="boolean", nullable=true, options={"default": true})
     * /
    private $active;
    public function getActive()
    {
        return $this->active;
    }
    public function setActive($flag)
    {
        $this->active = $flag;
    }
*/

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->getNumber();
    }

}
