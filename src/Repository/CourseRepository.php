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

        $filterTags = isset($filter['tags']) ? $filter['tags'] : [];
        $filterLimit = isset($filter['limit']) ? intval($filter['limit']) : null;
        $filterLevel = isset($filter['level']) ? intval($filter['level']) : null;
        $filterPage = isset($filter['page']) ? intval($filter['page']) : null;

        // Get Courses by Filter including Tags and count of Likes & Comments

        // (SELECT COALESCE(SUM(count), 0) FROM likes WHERE content_type = "news" AND content_id = n.id) AS likes_count,
        // (SELECT COALESCE(SUM(id), 0) FROM comments WHERE content_type = "news" AND content_id = n.id) AS comments_count,
        //    c.id as id
        $sql =
            'SELECT *
            FROM courses c';
/*
        if (count($filterTags)) {
            $sql .=
                ' JOIN news_tags nt on nt.news_id = n.id
                JOIN tags t on t.id = nt.tags_id
                WHERE t.tag in (:tags)
                AND active = true
                ORDER BY date DESC';
        } else */
        $sql .= ' WHERE active = true';

        if ($filterLevel)
            $sql .= " AND level = $filterLevel";

        $sql .= ' ORDER BY date DESC';

        if ($filterPage && $filterLimit) {
            $offset = $filterPage * $filterLimit;
            $sql .= " LIMIT $filterLimit OFFSET $offset";
        }
        else if ($filterLimit)
            $sql .= " LIMIT $filterLimit";

        $query = $conn->prepare($sql);

        $params = [];

//        if (count($filterTags))
//            $params = [ 'tags' => implode(', ', $filterTags) ];

        $query->execute($params);
        $courses = $query->fetchAll();

        // Select likes info for returned courses

        $ids = "";
        foreach ($courses as $row)
            $ids .= strval($row['id']) . ', ';
        $ids = trim($ids, ', ');

        $sql =
            'SELECT content_id
            FROM likes l
            WHERE content_type = "course"
            AND status = 1
            AND content_id in (' . $ids . ')';

//echo($sql);//die();

        $query = $conn->prepare($sql);
        $query->execute();
        $likes = $query->fetchAll();

        foreach ($courses as &$row) {
            $row['type'] = 'course';
            $row['like'] = in_array($row['id'], $likes) ? 1 : 0;
        }

        return $courses;
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
