<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="users", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci"})
 */
class User implements UserInterface, \Serializable
{
    const ROLE_USER = 'ROLE_USER';
    const ROLE_INVESTOR = 'ROLE_INVESTOR';
    const ROLE_ADMIN = 'ROLE_ADMIN';

    public static $roles = [
        self::ROLE_USER,
        self::ROLE_INVESTOR,
        self::ROLE_ADMIN,
    ];

    const PASSWORD_RAGEXP_VALIDATOR = '';

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $erc20Token;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $role;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    protected $confirmationCode;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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
     * @return User
     */
    public function setCreatedAt(\DateTime $createdAt): User
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
     * @param string $firstName
     * @return User
     */
    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $lastName
     * @return User
     */
    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $erc20Token
     * @return User
     */
    public function setErc20Token($erc20Token): User
    {
        $this->erc20Token = $erc20Token;

        return $this;
    }

    /**
     * @return string
     */
    public function getErc20Token()
    {
        return $this->erc20Token;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $role
     * @return User
     */
    public function setRole(string $role): User
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return [$this->role];
    }

    /**
     * @param string $confirmationCode
     * @return User
     */
    public function setConfirmationCode($confirmationCode): User
    {
        $this->confirmationCode = $confirmationCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getConfirmationCode()
    {
        return $this->confirmationCode;
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    public function eraseCredentials()
    {
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->createdAt,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->erc20Token,
            $this->password,
            $this->role,
        ]);
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->createdAt,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->erc20Token,
            $this->password,
            $this->role,
            ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    public function __toString()
    {
       return $this->email;
    }

}
