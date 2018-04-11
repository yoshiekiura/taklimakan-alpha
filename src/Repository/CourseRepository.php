<?php

namespace App\Repository;

use App\Entity\Course;
//use App\Entity\Tags;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Common\Collections\ArrayCollection;

class CourseRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Course::class);
    }

    public function getCourses($filter)
    {

        $em = $this->getEntityManager();
        $conn = $em->getConnection();
/*
        $filterTags = isset($filter['tags']) ? $filter['tags'] : [];
        $filterLimit = isset($filter['limit']) ? intval($filter['limit']) : null;

        // Get News by Filter including Tags and count of Likes & Comments

        $sql =
            'SELECT *,
            (SELECT COALESCE(SUM(count), 0) FROM likes WHERE content_type = "news" AND content_id = n.id) AS likes_count,
            (SELECT COALESCE(SUM(id), 0) FROM comments WHERE content_type = "news" AND content_id = n.id) AS comments_count,
            n.id as id
            FROM news n';

        if (count($filterTags)) {
            $sql .=
                ' JOIN news_tags nt on nt.news_id = n.id
                JOIN tags t on t.id = nt.tags_id
                WHERE t.tag in (:tags)
                AND active = true
                ORDER BY date DESC';
        } else
            $sql .= ' WHERE active = true
            ORDER BY date DESC';

        if ($filterLimit)
            $sql .= " LIMIT $filterLimit";

        $query = $conn->prepare($sql);

        $params = [];

        if (count($filterTags))
            $params = [ 'tags' => implode(', ', $filterTags) ];

        $query->execute($params);


        $tagsCollection = new ArrayCollection();

        $rows = $query->fetchAll();

        $sql =
            'SELECT tag
            FROM tags t
            INNER JOIN news_tags nt on nt.tags_id = t.id
            WHERE nt.news_id = :news_id';

        foreach ($rows as &$row) {

            $query = $conn->prepare($sql);
            $query->execute([
                'news_id' => $row['id'],
            ]);
            $tags = $query->fetchAll();

            $row['tags'] = $tags;
        }

        return $rows;
*/
    }

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
