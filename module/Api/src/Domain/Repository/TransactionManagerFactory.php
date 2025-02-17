<?php

/**
 * Class TransactionManager
 * @package Dvsa\Olcs\Api\Domain\Repository
 */
namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Dvsa\Olcs\Api\Domain\Exception;
use Doctrine\ORM\Query;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArraySerializableInterface as QryCmd;

/**
 * Class TransactionManager
 * @package Dvsa\Olcs\Api\Domain\Repository
 */
final class TransactionManagerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator, $name = null, $requestedName = null)
    {
        return new TransactionManager($serviceLocator->get('doctrine.entitymanager.orm_default'));
    }
}
