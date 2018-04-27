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
        $rows = $query->fetchAll();

        return $rows;
    }

}
