<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NewsRepository")
 */
class News
{

    // Init News

    public function __construct() {
        $this->date = new \DateTime();
        $this->tags = new ArrayCollection();
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
     * @ORM\Column(type="string", length=255)
     */
    private $title;
    public function getTitle()
    {
        return $this->title;
    }
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @ORM\Column(type="text")
     */
    private $lead;
    public function getLead()
    {
        return $this->lead;
    }
    public function setLead($lead)
    {
        $this->lead = $lead;
    }

    /**
     * @ORM\Column(type="text")
     */
    private $text;
    public function getText()
    {
        return $this->text;
    }
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;
    public function getImage()
    {
        return $this->image;
    }
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;
    public function getDate()
    {
        return $this->date;
    }
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @ORM\ManyToMany(targetEntity="Tags")
     */
    private $tags;
    public function getTags()
    {
        return $this->tags;
    }
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;
    public function getActive()
    {
        return $this->active;
    }
    public function setActive($flag)
    {
        $this->active = $flag;
    }


    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->getTitle();
    }

}
