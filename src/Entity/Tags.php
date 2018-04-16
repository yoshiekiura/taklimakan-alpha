<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TagsRepository")
 * @ORM\Table(name="tags", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci"}) 
 */
class Tags
{

    // Init Tags

    public function __construct() {
        // $this->date = new \DateTime();
        // $this->comments = new ArrayCollection();
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
    private $tag;
    public function getTag()
    {
        return $this->tag;
    }
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->getTag();
    }

}
