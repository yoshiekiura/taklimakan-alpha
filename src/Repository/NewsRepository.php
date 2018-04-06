<?php

namespace App\Repository;

use App\Entity\News;
use App\Entity\Tags;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

use Doctrine\ORM\PersistentCollection;
//use Doctrine\ORM\ArrayCollection;
use Doctrine\Common\Collections\ArrayCollection;



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
/*
    public function getNews($limit = 0)
    {
        // FIXME We have to have more advanced filter here (limits, ranges, categories, etc)
        // $sql = "SELECT name FROM user WHERE favorite_color = :color";
        //  ORDER BY date DESC LIMIT

        $sql =
            'SELECT *,
            (SELECT COALESCE(SUM(count), 0) FROM likes WHERE content_type = "news" AND content_id = news.id) AS likes_count,
            (SELECT COALESCE(SUM(id), 0) FROM comments WHERE content_type = "news" AND content_id = news.id) AS comments
            FROM news';
//echo $sql; die();
        // $params['color'] = blue;
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        // $query->execute($params);
        $query->execute();

        return $query->fetchAll();
    }
*/
    /**
     * @return News[] Returns an array of News objects NB! WITH LIKES
     */
    public function getNews($filter)
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();

        $filterTags = isset($filter['tags']) ? $filter['tags'] : [];

        // Get News by Filter including Tags and count of Likes & Comments

        $sql =
            'SELECT *,
            (SELECT COALESCE(SUM(count), 0) FROM likes WHERE content_type = "news" AND content_id = n.id) AS likes_count,
            (SELECT COALESCE(SUM(id), 0) FROM comments WHERE content_type = "news" AND content_id = n.id) AS comments_count
            FROM news n';

        if (count($filterTags))
            $sql .=
                ' JOIN news_tags nt on nt.news_id = n.id
                JOIN tags t on t.id = nt.tags_id
                WHERE t.tag in (:tags)
                GROUP BY n.id';

        // $sql .= ' GROUP BY n.id';
//echo $sql; die();

        $query = $conn->prepare($sql);
        $query->execute([
            'tags' => implode(', ', $filterTags),
        ]);

        // $tagsCollection = new PersistentCollection($em, Tags::class, []);
        $tagsCollection = new ArrayCollection(); // $em, Tags::class, []

        $rows = $query->fetchAll();

        $sql =
            'SELECT tag
            FROM tags t
            INNER JOIN news_tags nt on nt.tags_id = t.id
            WHERE nt.news_id = :news_id';

        foreach ($rows as &$row) {
echo " | " . $row['id'];
            $query = $conn->prepare($sql);
            $query->execute([
                'news_id' => $row['id'],
            ]);
            $tags = $query->fetchAll();
//var_dump($tags);die();
            $row['tags'] = $tags;
        }

        return $rows;
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