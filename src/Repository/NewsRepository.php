<?php

namespace App\Repository;

use App\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method News|null find($id, $lockMode = null, $lockVersion = null)
 * @method News|null findOneBy(array $criteria, array $orderBy = null)
 * @method News[]    findAll()
 * @method News[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, News::class);
    }

    /**
     * @return News[] Returns an array of News objects NB! WITH LIKES
     */
    public function getNews($limit = 0)
    {
        // FIXME We have to have more advanced filter here (limits, ranges, categories, etc)
        // $sql = "SELECT name FROM user WHERE favorite_color = :color";
        //  ORDER BY date DESC LIMIT

        $sql =
            'SELECT *,
            (SELECT COALESCE(SUM(count), 0) FROM likes WHERE content_type = "news" AND content_id = news.id) AS likes,
            (SELECT COALESCE(SUM(id), 0) FROM comments WHERE content_type = "news" AND content_id = news.id) AS comments
            FROM news';

        // $params['color'] = blue;
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        // $query->execute($params);
        $query->execute();

        return $query->fetchAll();
    }

    /**
     * @return News[] Returns an array of News objects NB! WITH LIKES
     */
    public function getNewsByFilter($tags = [])
    {
        //            (SELECT COALESCE(SUM(count), 0) FROM likes WHERE content_type = "news" AND content_id = news.id) AS likes,
        //            (SELECT COALESCE(SUM(id), 0) FROM comments WHERE content_type = "news" AND content_id = news.id) AS comments
        // WHERE ';

        $sql =
            'SELECT *
            FROM news n
            JOIN news_tags nt on nt.news_id = n.id
            JOIN tags t on t.id = nt.tags_id';
        if (count($tags))
            $sql .= ' WHERE tag in (:tags)';
        $sql .= ' GROUP BY n.id';
//echo $sql; die();
        // $params['color'] = blue;
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        // $query->execute($params);
        $query->execute([
            'tags' => implode(', ', $tags),
        ]);

        return $query->fetchAll();
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
