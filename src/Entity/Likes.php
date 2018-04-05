<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LikesRepository")
 */
class Likes
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
     * @ORM\Column(type="string", length=32, nullable=false)
     */
    private $content_type;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"unsigned": true})
     */
    private $user_id;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"unsigned": true})
     */
    private $content_id;

    /**
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned": true, "default": 0})
     */
    private $count;

    // Not so good : options={"default"="CURRENT_TIMESTAMP"}
    // Does not work : options={"default": 0}
    // See https://stackoverflow.com/questions/7698625/doctrine-2-1-datetime-column-default-value

    /**
     * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"})
     */
    private $date;

}
