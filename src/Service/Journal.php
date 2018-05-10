<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;

class Journal
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param User $user
     * @param int $action
     * @param array $data
     * @param User|null $admin
     * @throws \Exception
     */
    public function log(User $user, int $action, array $data, User $admin = null)
    {
        if (!in_array($action, \App\Entity\Journal::$actions)) {
            throw new \Exception("Unknown action \"{$action}\"");
        }

        $row = (new \App\Entity\Journal())
            ->setAction($action)
            ->setUser($user)
            ->setData($data)
            ->setAdmin($admin)
        ;

        $em = $this->doctrine->getManager();
        $em->persist($row);
        $em->flush();
    }
}
