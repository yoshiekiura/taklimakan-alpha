<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="journal", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci"})
 */
class Journal
{
    const ACTION_CHANGE_USER_FIRST_NAME = 1;
    const ACTION_CHANGE_USER_LAST_NAME = 2;
    const ACTION_CHANGE_USER_ERC20_TOKEN = 3;
    const ACTION_CHANGE_USER_PASSWORD = 4;

    public static $actions = [
        self::ACTION_CHANGE_USER_FIRST_NAME,
        self::ACTION_CHANGE_USER_LAST_NAME,
        self::ACTION_CHANGE_USER_ERC20_TOKEN,
        self::ACTION_CHANGE_USER_PASSWORD,
    ];

    /**
     * @var int
     *
     * @ORM\Column(type="id")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="admin_id", referencedColumnName="id", nullable=true)
     */
    protected $admin;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    protected $action;

    /**
     * @var array
     *
     * @ORM\Column(type="array", nullable=false)
     */
    protected $data;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->data = [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $createdAt
     * @return Journal
     */
    public function setCreatedAt(\DateTime $createdAt): Journal
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param User $admin
     * @return Journal
     */
    public function setAdmin(User $admin): Journal
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @return User
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @param User $user
     * @return Journal
     */
    public function setUser(User $user): Journal
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $action
     * @return Journal
     */
    public function setAction(int $action): Journal
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return int
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param array $data
     * @return Journal
     */
    public function setData(array $data): Journal
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
