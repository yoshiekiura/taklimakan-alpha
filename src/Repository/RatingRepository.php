<?php

namespace App\Repository;

use App\Entity\Rating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
//use Doctrine\DBAL\DriverManager;

use Symfony\Component\Security\Core\User\UserInterface;

class RatingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Likes::class);
    }

/*
    public function getLikes($content_type, $content_id)
    {
        $conn = $this->getEntityManager()->getConnection();
        $likes = $conn->fetchColumn(
            'SELECT SUM(count) FROM likes WHERE content_type = ? AND content_id = ? ',
            [$content_type, $content_id],
            0
        );

        return intval($likes);
    }
*/

/*
    public function getRating($type, $id, $user)
    {
        if ($user === null)
            return 0;

        if ($user instanceof UserInterface)
            $user = $user->getId();

        $conn = $this->getEntityManager()->getConnection();

        $like = $conn->fetchColumn(
            'SELECT status FROM likes WHERE content_type = ? AND content_id = ? AND user_id = ?',
            [$type, $id, $user],
            0
        );

        return intval($like);
    }
*/

}
