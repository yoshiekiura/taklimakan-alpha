<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
//use Doctrine\Common\Collections\ArrayCollection;
//use App\Entity\Likes;

// Trying to use right association to link Courses and Lessons together
// https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/association-mapping.html
// NB! To have real flexibility we'll start without any hard mapping between them!

/**
 * @ORM\Entity(repositoryClass="App\Repository\CourseRepository")
 * @ORM\Table(name="courses")
 */
class Course
{

    public function __construct() {
//        $this->em = $em;
        $this->date = new \DateTime();
//        $this->tags = new ArrayCollection();
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

    // Source URL
    // We could get Name from domain and link short description to it

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

    // Date of Creation or Update ?

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

    // Tags as plain text separated with commas

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
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

    // There are common Categories between News, Analytics section and Courses?

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

    // Complexity Level : 0 = NOT_DEFINED | 1 = LOW | 2 = MEDIUM | 3 = HIGH

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default": 0})
     */
    private $level;
    public function getLevel()
    {
        return $this->level;
    }
    public function setLevel($level)
    {
        $this->level = $level;
    }

    // Price in base currency (USD?) like $99.95

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2, options={"default": 0.0})
     */
    private $price;
    public function getPrice()
    {
        return $this->price;
    }
    public function setPrice($price)
    {
        $this->price = $price;
    }

    // Virtual Property to aggregate Rating from external Stars table

    private $stars;
    public function getStars()
    {
        return $this->stars;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->getTitle();
    }

}
