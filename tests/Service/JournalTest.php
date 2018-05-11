<?php
/*
   This file contains tests for /src/Service/Journal.php module
*/
namespace App\Service;

use App\Service;
use App\tests\Service\Stubs;
use Doctrine\ORM\EntityManager;
use App\Entity\User;
//use Doctrine\Common\Persistence\ManagerRegistry;

use PHPUnit\Framework\TestCase;

class JournalTest extends TestCase
{
  /**
   * @expectedException \Exception
   * @expectedExceptionMessageRegExp /Unknown action \".*\"/
   */
  public function testCase1()
  {
    $managerRegistry = new ManagerRegistryTest();
    $user = new User();
    $array_data = ['dummyArrayData'];
    $journal = new Journal($managerRegistry);
    $journal->log($user, 0, $array_data);
  }

  public function testCase2()
  {
    $managerRegistry = new ManagerRegistryTest();
    $user = new User();
    $array_data = ['dummyArrayData'];
//    (new \App\Entity\Journal())->setAction("0");

    $journal = new Journal($managerRegistry);
    $journal->log($user, 1, $array_data);
    
    $this->assertEquals($managerRegistry->entityManager->flush_called, 1);
  }
}
