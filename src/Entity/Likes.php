<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LikesRepository")
 * @ORM\Table(name="likes", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci"}, uniqueConstraints={@UniqueConstraint(name="unique_like", columns={"content_type", "content_id", "user_id"})})
 */
class Likes
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
     * @ORM\Column(type="integer", nullable=false, options={"unsigned": true})
     */
    private $user_id;
    public function getUserId()
    {
        return $this->user_id;
    }
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }


    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $content_type;
    public function getType()
    {
        return $this->content_type;
    }
    public function setType($type)
    {
        $this->content_type = $type;
    }

    /**
     * @ORM\Column(type="integer", nullable=false, options={"unsigned": true})
     */
    private $content_id;
    public function getContentId()
    {
        return $this->content_id;
    }
    public function setContentId($content_id)
    {
        $this->content_id = $content_id;
    }


    /**
     * @ORM\Column(type="boolean", nullable=true, options={"unsigned": true})
     */
    private $status;
    public function getStatus()
    {
        return $this->status;
    }
    public function setStatus($status)
    {
        $this->status = $status;
    }


    /**
     * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"}, nullable=true)
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


}
