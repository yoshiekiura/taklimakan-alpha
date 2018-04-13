<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LikesRepository")
 * @ORM\Table(name="likes", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci"})
 */
class Likes
{

    // Init Tags

    public function __construct() {
        // $this->date = new \DateTime();
        // $this->comments = new ArrayCollection();
    }

    // Complex Keys
    // http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/composite-primary-keys.html
    // * * @ORM\Id @ORM\Column(type="string") * /
    // private $name;
    // * * @ORM\Id @ORM\Column(type="integer") * /
    // private $year;

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
