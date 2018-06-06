<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProviderRepository")
 * @ORM\Table(name="providers", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci"})
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
     * @var string
     * @ORM\Column(type="string", length=32, nullable=true)
     */

    private $type;

    public function getType()
    {
        return $this->type;
    }
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @ORM\Column(type="string", length=100, nullable=true)
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
     * @ORM\Column(type="string", length=100)
     */
    private $slug;
    public function getSlug()
    {
        return $this->slug;
    }
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @ORM\Column(type="text", nullable=true)
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
     * @ORM\Column(type="text", nullable=true)
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
     * @var string
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
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */

    private $video;

    public function getVideo()
    {
        return $this->video;
    }
    public function setVideo($video)
    {
        $this->video = $video;
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
