<?php

namespace App\Repository;

use App\Entity\Likes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
//use Doctrine\DBAL\DriverManager;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Likes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Likes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Likes[]    findAll()
 * @method Likes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LikesRepository extends ServiceEntityRepository
{
    // private $conn = null;

    public function __construct(RegistryInterface $registry)
    {
        //$this->conn = DriverManager::getConnection($params, $config);
        parent::__construct($registry, Likes::class);
    }


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

    public function getState($type, $id, $user)
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

    public function like($content_type, $content_id, $user_id)
    {

        $conn = $this->getEntityManager()->getConnection();
        $query = $conn->prepare('INSERT IGNORE INTO likes (status, content_type, content_id, user_id) VALUES (1, ?, ?, ?)');

        $query->bindValue(1, $content_type);
        $query->bindValue(2, $content_id);
        $query->bindValue(3, $user_id);

        $query->execute();

        // 'INSERT IGNORE INTO likes (count, content_type, content_id, user_id) VALUES (1, :content_type, :content_id, :user_id)'
        // ['content_type' => $content_type, 'content_id' => $content_id, 'user_id' => $user_id])

/*
        $likes = $conn->exec(
            'INSERT IGNORE INTO likes (count, content_type, content_id, user_id) VALUES (1, ?, ?, ?)',
            [$content_type, $content_id, $user_id],
            0
        ); */
    }

    public function dislike($content_type, $content_id, $user_id)
    {

        $conn = $this->getEntityManager()->getConnection();
        $query = $conn->prepare('DELETE FROM likes WHERE status = 1 AND content_type = ? AND content_id = ? AND user_id = ? LIMIT 1');

        $query->bindValue(1, $content_type);
        $query->bindValue(2, $content_id);
        $query->bindValue(3, $user_id);

        $query->execute();

        // 'INSERT IGNORE INTO likes (count, content_type, content_id, user_id) VALUES (1, :content_type, :content_id, :user_id)'
        // ['content_type' => $content_type, 'content_id' => $content_id, 'user_id' => $user_id])

/*
        $likes = $conn->exec(
            'INSERT IGNORE INTO likes (count, content_type, content_id, user_id) VALUES (1, ?, ?, ?)',
            [$content_type, $content_id, $user_id],
            0
        ); */
    }



//    /**
//     * @return News[] Returns an array of News objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?News
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
