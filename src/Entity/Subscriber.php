<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriberRepository")
 * @ORM\Table(name="subscribers", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci"}, uniqueConstraints={@UniqueConstraint(name="idx", columns={"type", "email", "user_id"})})
 */
class Subscriber
{

    public function __construct() {
        $this->date = new \DateTime();
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
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     */

    private $email;
    public function getEmail()
    {
        return $this->email;
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }


    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default": false})
     */

    // Does Subscriber active now?

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
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     */

    // Verification code if needed (optional)

    private $code;
    public function getCode()
    {
        return $this->code;
    }
    public function setCode($code)
    {
        $this->code = $code;
    }


    // Date of Creation or Update ?

    /**
     * @ORM\Column(type="datetime", nullable=true)
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
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->getEmail();
    }

}
