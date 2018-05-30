<?php
// stub for ManagerRegister
namespace App\Service;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

class EntityManagerTest
{
  public $flush_called = 0;
  public function persist(Object $obj)
  {
    $this->flush_called = 0;
  }
  public function flush()
  {
    $this->flush_called++;
  }
}

class ManagerRegistryTest implements ManagerRegistry
{
  public $entityManager;

  //public function __construct(EntityManager $entityManager)
  public function __construct()
  {
    $this->entityManager = new EntityManagerTest();
  }
  
  public function getDefaultConnectionName()
  {
    return 'defaultConnectionName';
  }
  public function getConnection($name=null)
  {
    //return $this->entityManager->getConnection();
    return null;
  }
  public function getConnections()
  {
    //return [$this->getDefaultConnectionName() => $this->getConnection()];
    return null;
  }

  public function getConnectionNames()
  {
    //return [$this->getDefaultConnectionName()];
    return null;
  }

  public function getDefaultManagerName()
  {
     return 'defaultManagerName';
  }
  public function getManager($name = null)
  {
    return $this->entityManager;
    //return null;
  }
  public function getManagers()
  {
    return [$this->getDefaultManagerName() => $this->entityManager]; 
    //return null;
  }
  public function resetManager($name=null)
  {
    return 'dummyReset';
  }
  public function getAliasNamespace($alias)
  {
    return 'App';
  }
  public function getManagerNames()
  {
    return [$this->getDefaultManagerName()];
    //return null;
  }
  public function getRepository($persistentObject, $persistentManagerName = null)
  {
    //return $this->entityManager->getRepository(ClassUtils::getRealClass($persistentObject));
    return null;
  }
  public function getManagerForClass($class)
  {
    //return $this->entityManager->getRepository($class);
    return null;
  }
}

