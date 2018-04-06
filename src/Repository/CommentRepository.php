<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
//use Doctrine\DBAL\DriverManager;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Comment::class);
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

    public function like($content_type, $content_id, $user_id)
    {

        $conn = $this->getEntityManager()->getConnection();
        $query = $conn->prepare('INSERT IGNORE INTO likes (count, content_type, content_id, user_id) VALUES (1, ?, ?, ?)');

        $query->bindValue(1, $content_type);
        $query->bindValue(2, $content_id);
        $query->bindValue(3, $user_id);

        $query->execute();

        // 'INSERT IGNORE INTO likes (count, content_type, content_id, user_id) VALUES (1, :content_type, :content_id, :user_id)'
        // ['content_type' => $content_type, 'content_id' => $content_id, 'user_id' => $user_id])

    }

    public function dislike($content_type, $content_id, $user_id)
    {

        $conn = $this->getEntityManager()->getConnection();
        $query = $conn->prepare('DELETE FROM likes WHERE count = 1 AND content_type = ? AND content_id = ? AND user_id = ? LIMIT 1');

        $query->bindValue(1, $content_type);
        $query->bindValue(2, $content_id);
        $query->bindValue(3, $user_id);

        $query->execute();

        // 'INSERT IGNORE INTO likes (count, content_type, content_id, user_id) VALUES (1, :content_type, :content_id, :user_id)'
        // ['content_type' => $content_type, 'content_id' => $content_id, 'user_id' => $user_id])


    }

*/

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
