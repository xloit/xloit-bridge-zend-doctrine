<?php
/**
 * This source file is part of Xloit project.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * <http://www.opensource.org/licenses/mit-license.php>
 * If you did not receive a copy of the license and are unable to obtain it through the world-wide-web,
 * please send an email to <license@xloit.com> so we can send you a copy immediately.
 *
 * @license   MIT
 * @link      http://xloit.com
 * @copyright Copyright (c) 2016, Xloit. All rights reserved.
 */

namespace Xloit\Bridge\Zend\Session\SaveHandler\Database\Adapter;

use Closure;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Xloit\Bridge\Zend\Session\Exception;

/**
 * A {@link Doctrine} class
 *
 * @package Xloit\Bridge\Zend\Session\SaveHandler\Database\Adapter
 */
class Doctrine extends AbstractAdapter
{
    /**
     *
     *
     * @var EntityRepository
     */
    protected $repository;

    /**
     * Select all entities
     *
     * @return array
     */
    public function selectAll()
    {
        return $this->selectBy();
    }

    /**
     * Select entities by a set of criteria.
     *
     * @param Closure|string|array $criteria
     *
     * @return array
     */
    public function selectBy($criteria = null)
    {
        return $this->getRepository()->findBy($criteria);
    }

    /**
     * Gets the repository for an entity class.
     *
     * @return EntityRepository
     */
    public function getRepository()
    {
        if (null === $this->repository) {
            /** @noinspection PhpParamsInspection */
            $this->setRepository($this->storageManager->getRepository($this->options->getClassName()));
        }

        return $this->repository;
    }

    /**
     * Sets the Repository value
     *
     * @param EntityRepository $repository
     *
     * @return static
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Select entity by a set of criteria.
     *
     * @param Closure|string|array $criteria
     *
     * @return mixed
     */
    public function select($criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Insert a new record
     *
     * @param mixed $sessionEntity
     *
     * @return mixed
     * @throws \Xloit\Bridge\Zend\Session\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function insert($sessionEntity)
    {
        /** @var EntityManager $storageManager */
        $storageManager = $this->storageManager;

        if (is_array($sessionEntity)) {
            $sessionEntity = $this->toEntity($sessionEntity);
        }

        $this->validateEntity($sessionEntity, __METHOD__);

        $storageManager->persist($sessionEntity);
        $storageManager->flush($sessionEntity);

        return $sessionEntity;
    }

    /**
     * Update a record
     *
     * @param mixed $sessionEntity
     * @param array $sessionData
     *
     * @return mixed
     * @throws \Xloit\Bridge\Zend\Session\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function update($sessionEntity, $sessionData)
    {
        $this->validateEntity($sessionEntity, __METHOD__);

        /** @var EntityManager $storageManager */
        $storageManager = $this->storageManager;
        $classMetadata  = $storageManager->getClassMetadata($this->options->getClassName());

        foreach ($sessionData as $fieldName => $data) {
            $classMetadata->setFieldValue($sessionEntity, $fieldName, $data);
        }

        $storageManager->persist($sessionEntity);
        $storageManager->flush($sessionEntity);

        return $sessionEntity;
    }

    /**
     * Delete a record
     *
     * @param mixed $sessionEntity
     *
     * @return void
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Xloit\Bridge\Zend\Session\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function delete($sessionEntity)
    {
        /** @var EntityManager $storageManager */
        $storageManager = $this->storageManager;

        if (is_array($sessionEntity)) {
            $sessionEntity = $this->toEntity($sessionEntity);
        }

        $this->validateEntity($sessionEntity, __METHOD__);

        $sessionEntity = $storageManager->find($this->options->getClassName(), $this->getIdValue($sessionEntity));
        $storageManager->remove($sessionEntity);
        $storageManager->flush($sessionEntity);
    }

    /**
     * Garbage Collection - remove old session data older than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime
     *
     * @return bool
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Xloit\Bridge\Zend\Session\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function gc($maxlifetime)
    {
        $queryBuilder = $this->getRepository()->createQueryBuilder('session');

        $sessions = $queryBuilder->where(
            $queryBuilder->expr()->lt('session.' . $this->getOptions()->getModifiedColumn(), time() - $maxlifetime)
        )->getQuery()->getResult();

        foreach ($sessions as $session) {
            $this->delete($session);
        }

        return true;
    }

    /**
     * Returns the session id
     *
     * @param mixed $sessionEntity
     *
     * @return string|int
     */
    public function getIdValue($sessionEntity)
    {
        /** @var EntityManager $storageManager */
        $storageManager = $this->storageManager;
        $classMetadata  = $storageManager->getClassMetadata($this->options->getClassName());

        return $classMetadata->getFieldValue($sessionEntity, $this->options->getIdColumn());
    }

    /**
     * Returns the session created Unix timestamp
     *
     * @param mixed $sessionEntity
     *
     * @return int
     */
    public function getCreatedValue($sessionEntity)
    {
        /** @var EntityManager $storageManager */
        $storageManager = $this->storageManager;
        $classMetadata  = $storageManager->getClassMetadata($this->options->getClassName());

        return $classMetadata->getFieldValue($sessionEntity, $this->options->getCreatedColumn());
    }

    /**
     * Returns the session data
     *
     * @param mixed $sessionEntity
     *
     * @return string
     */
    public function getDataValue($sessionEntity)
    {
        /** @var EntityManager $storageManager */
        $storageManager = $this->storageManager;
        $classMetadata  = $storageManager->getClassMetadata($this->options->getClassName());

        return $classMetadata->getFieldValue($sessionEntity, $this->options->getDataColumn());
    }

    /**
     * Returns the session name
     *
     * @param mixed $sessionEntity
     *
     * @return string
     */
    public function getNameValue($sessionEntity)
    {
        /** @var EntityManager $storageManager */
        $storageManager = $this->storageManager;
        $classMetadata  = $storageManager->getClassMetadata($this->options->getClassName());

        return $classMetadata->getFieldValue($sessionEntity, $this->options->getClassName());
    }

    /**
     * Returns the session lifetime
     *
     * @param mixed $sessionEntity
     *
     * @return int
     */
    public function getLifetimeValue($sessionEntity)
    {
        /** @var EntityManager $storageManager */
        $storageManager = $this->storageManager;
        $classMetadata  = $storageManager->getClassMetadata($this->options->getClassName());

        return $classMetadata->getFieldValue($sessionEntity, $this->options->getLifetimeColumn());
    }

    /**
     * Returns the session modified Unix timestamp
     *
     * @param mixed $sessionEntity
     *
     * @return int
     */
    public function getModifiedValue($sessionEntity)
    {
        /** @var EntityManager $storageManager */
        $storageManager = $this->storageManager;
        $classMetadata  = $storageManager->getClassMetadata($this->options->getClassName());

        return $classMetadata->getFieldValue($sessionEntity, $this->options->getModifiedColumn());
    }

    /**
     * Returns the session modified Unix timestamp
     *
     * @param array $data
     *
     * @return mixed
     */
    protected function toEntity($data)
    {
        /** @var EntityManager $storageManager */
        $storageManager = $this->storageManager;
        $className      = $this->options->getClassName();
        $entity         = new $className();
        $classMetadata  = $storageManager->getClassMetadata($className);

        foreach ($data as $key => $value) {
            $classMetadata->setFieldValue($entity, $key, $value);
        }

        return $entity;
    }

    /**
     *
     *
     * @param mixed $entity
     *
     * @return bool
     */
    protected function isEntity($entity)
    {
        /** @var EntityManager $storageManager */
        $storageManager = $this->storageManager;

        return !$storageManager->getMetadataFactory()->isTransient(ClassUtils::getClass($entity));
    }

    /**
     * Indicates whether the given entity is valid
     *
     * @internal
     *
     * @param mixed  $entity
     * @param string $method
     *
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    private function validateEntity($entity, $method)
    {
        $className = $this->options->getClassName();

        if (!($entity instanceof $className) || !$this->isEntity($entity)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s expects parameter 1 to be an instance of %s, %s provided instead',
                    $method,
                    $className,
                    is_object($entity) ? get_class($entity) : gettype($entity)
                )
            );
        }
    }
}
