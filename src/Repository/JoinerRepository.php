<?php

namespace App\Repository;

use App\Entity\Joiner;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
//use Doctrine\Common\Collections\ArrayCollection;

// NB! We suppose that we'll have some global static methods here like Joiner::Join(Course, Lecture)?

class JoinerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Joiner::class);
    }
}
