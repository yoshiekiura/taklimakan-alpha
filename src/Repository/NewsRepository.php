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
    public function getNews($filter)
    {

        // Select NEWS with default filter ACTIVE = TRUE and sorting by DATE = DESC

        $em = $this->getEntityManager();
        $conn = $em->getConnection();

        $filterTags = isset($filter['tags']) ? $filter['tags'] : [];
        $filterLimit = isset($filter['limit']) ? intval($filter['limit']) : null;
        $filterPage = isset($filter['page']) ? intval($filter['page']) : null;

        // Get News by Filter including Tags and count of Likes & Comments

        $sql =
            'SELECT *,
            (SELECT COALESCE(SUM(count), 0) FROM likes WHERE content_type = "news" AND content_id = n.id) AS likes_count,
            (SELECT COALESCE(SUM(id), 0) FROM comments WHERE content_type = "news" AND content_id = n.id) AS comments_count,
            n.id as id
            FROM news n';

        if (count($filterTags)) {
            $sql .=
                ' WHERE tags LIKE :tags
                AND active = true
                ORDER BY date DESC';
        } else
            $sql .= ' WHERE active = true
            ORDER BY date DESC';

        if ($filterPage && $filterLimit) {
            $offset = $filterPage * $filterLimit;
            $sql .= " LIMIT $filterLimit OFFSET $offset";
        }
        else if ($filterLimit)
            $sql .= " LIMIT $filterLimit";

        $query = $conn->prepare($sql);
        $params = [];
        // FIXME If there are a few tags we have to use looping here instead of implode
        if (count($filterTags))
            //$params = [ 'tags' => implode(', ', $filterTags) ];
            $params = [ 'tags' => '%'.$filterTags[0].'%' ];
        $query->execute($params);
        $tagsCollection = new ArrayCollection(); // $em, Tags::class, []
        $rows = $query->fetchAll();

/*
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
*/

        // Create tags array from string, trimming commas and whitespace
        foreach ($rows as &$row)
            $row['tags'] = array_map('trim', explode(',', $row['tags']));

        return $rows;
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
    */

}
