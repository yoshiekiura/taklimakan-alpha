<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

// NB! We allow only FLAT comments for the moment

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 * @ORM\Table(name="comments", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci"})
 */
class Comment
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
     * @ORM\Column(type="string", length=32, nullable=false)
     */
    private $content_type;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"unsigned": true})
     */
    private $content_id;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"unsigned": true})
     */
    private $user_id;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $text;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default": true})
     */
    private $active;
    public function show()
    {
        $this->active = true;
    }
    public function hide()
    {
        $this->active = false;
    }

    /**
     * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"})
     */
    private $date;

    public function setComment($content_type, $content_id, $user_id, $text)
    {
        $this->content_type = $content_type;
        $this->content_if = $content_id;
        $this->user_id = $user_id;
        $this->text = $text;
        $this->active = true;
        $this->date = new DateTime();
    }

}
