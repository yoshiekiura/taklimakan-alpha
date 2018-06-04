<?php

namespace App\Repository;

use App\Entity\Lecture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Common\Collections\ArrayCollection;

class LectureRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Lecture::class);
    }

    public function getLectures($filter)
    {

        $em = $this->getEntityManager();
        $conn = $em->getConnection();

        $filterTags = isset($filter['tags']) ? $filter['tags'] : [];
        $filterLimit = isset($filter['limit']) ? intval($filter['limit']) : null;
        $filterLevel = isset($filter['level']) ? intval($filter['level']) : null;
        $filterPage = isset($filter['page']) ? intval($filter['page']) : null;
        $filterCourse = isset($filter['course']) ? intval($filter['course']) : null;
        $filterUser = isset($filter['user']) ? intval($filter['user']) : null;

        $sql = 'SELECT * FROM lectures l WHERE active = true';

        if ($filterLevel)
            $sql .= " AND level = $filterLevel";

        if ($filterCourse)
            $sql .= " AND course_id = $filterCourse";
        else
            $sql .= " AND course_id IS NULL";

        $sql .= ' ORDER BY date DESC';

        if ($filterPage && $filterLimit) {
            $offset = $filterPage * $filterLimit;
            $sql .= " LIMIT $filterLimit OFFSET $offset";
        }
        else if ($filterLimit)
            $sql .= " LIMIT $filterLimit";

        $query = $conn->prepare($sql);

        $query->execute();
        $lectures = $query->fetchAll();

        // Select likes info for returned lectures
        if ($filterUser) {

            $ids = "";
            foreach ($lectures as $row)
                $ids .= strval($row['id']) . ', ';
            $ids = trim($ids, ', ');

            $sql =
                'SELECT content_id
                FROM likes l
                WHERE content_type = "lecture"
                AND user_id = ' . $filterUser
                . ' AND status = 1
                AND content_id in (' . $ids . ')';

            $query = $conn->prepare($sql);
            $query->execute();
            $likes = $query->fetchAll(\PDO::FETCH_COLUMN);

        }

        // Select ratings
        $sql =
            'SELECT content_id as id, AVG(rating) as rating
            FROM ratings
            WHERE content_type = "lecture"
            GROUP BY content_type, content_id';
//            AND content_id in (' . $ids . ')'; // FIXME! Test and enable this condition

        $query = $conn->prepare($sql);
        $query->execute();
        $ratings = $query->fetchAll();
//var_dump($ratings);


        foreach ($lectures as &$row) {
            $row['type'] = 'lecture';
            $row['like'] = $filterUser ? (in_array($row['id'], $likes) ? 1 : 0) : 0;

            $row['rating'] = 0;
            foreach ($ratings as $rating)
                if ($rating['id'] == $row['id'])
                    $row['rating'] = intval($rating['rating']);
            
        }

        return $lectures;
    }

}
