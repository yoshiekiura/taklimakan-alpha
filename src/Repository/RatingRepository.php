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
        parent::__construct($registry, Rating::class);
    }

    // Get rating for one Entry
    // Input: ContentType, ContentID, User or UserID

    public function getRating($type, $id, $user = null)
    {
        if ($user instanceof UserInterface)
            $user = $user->getId();

        $conn = $this->getEntityManager()->getConnection();

        // FIXME! Use WHERE to exclude rows with NULL and ZERO ratings
        $rating = $conn->fetchColumn(
            'SELECT AVG(rating) FROM ratings WHERE content_type = ? AND content_id = ?',
            [$type, $id],
            0
        );

        return intval($rating);
    }

}
