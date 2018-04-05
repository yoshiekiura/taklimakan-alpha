<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

use App\Entity\Likes;
use App\Entity\Comment;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnalyticsRepository")
 */
class Analytics
{

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $source;
    public function getSource()
    {
        return $this->source;
    }
    public function setSource($source)
    {
        $this->source = $source;
    }


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
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
     * @ORM\ManyToOne(targetEntity="Category")
     */
    private $category;
    public function getCategory()
    {
        return $this->category;
    }
    public function setCategory($category)
    {
        $this->category = $category;
    }


    /**
     * @ORM\Column(type="boolean", options={"default": false})
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

    public function getLikes()
    {
    }


    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->getTitle();
    }

}
