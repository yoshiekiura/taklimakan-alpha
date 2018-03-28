<?php

namespace App\Repository;

use App\Entity\Analytics;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Analytics|null find($id, $lockMode = null, $lockVersion = null)
 * @method Analytics|null findOneBy(array $criteria, array $orderBy = null)
 * @method Analytics[]    findAll()
 * @method Analytics[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnalyticsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Analytics::class);
    }

    /**
     * @return Analytics[] Returns an array of Analytics objects NB! WITH LIKES
     */
    public function getAnalytics($limit = 0)
    {
        // FIXME We have to have more advanced filter here (limits, ranges, categories, etc)
        // $sql = "SELECT name FROM user WHERE favorite_color = :color";
        //  ORDER BY date DESC LIMIT

        $sql =
            'SELECT *,
            (SELECT COALESCE(SUM(count), 0) FROM likes WHERE content_type = "analytics" AND content_id = analytics.id) AS likes,
            (SELECT COALESCE(SUM(id), 0) FROM comments WHERE content_type = "analytics" AND content_id = analytics.id) AS comments
            FROM analytics';

        // $params['color'] = blue;
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        // $query->execute($params);
        $query->execute();

        return $query->fetchAll();
    }





//    /**
//     * @return Analytics[] Returns an array of Analytics objects
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
    public function findOneBySomeField($value): ?Analytics
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
