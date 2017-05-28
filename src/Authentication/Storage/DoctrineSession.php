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

namespace Xloit\Bridge\Zend\Authentication\Storage;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Xloit\Std\Interop\Object\EntityInterface;

/**
 * A {@link DoctrineSession} class.
 *
 * @package Xloit\Bridge\Zend\Authentication\Storage
 */
class DoctrineSession extends Session
{
    /**
     * A valid object implementing ObjectRepository interface (or ObjectManager/identityClass).
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * A valid object implementing ObjectRepository interface (or ObjectManager/identityClass).
     *
     * @var EntityRepository
     */
    protected $objectRepository;

    /**
     * If an objectManager is not supplied, this metadata will be used by {@link DoctrineSession}.
     *
     * @var ClassMetadata
     */
    protected $classMetadata;

    /**
     * Entity's class name.
     *
     * @var string
     */
    protected $identityClass;

    /**
     *
     *
     * @return EntityRepository
     */
    public function getObjectRepository()
    {
        if (null === $this->objectRepository) {
            /** @var EntityRepository $repository */
            $repository = $this->getObjectManager()->getRepository($this->getIdentityClass());

            $this->setObjectRepository($repository);
        }

        return $this->objectRepository;
    }

    /**
     *
     *
     * @param EntityRepository $objectRepository
     *
     * @return $this
     */
    public function setObjectRepository(EntityRepository $objectRepository)
    {
        $this->objectRepository = $objectRepository;

        return $this;
    }

    /**
     *
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     *
     *
     *
     * @param ObjectManager $objectManager
     *
     * @return $this
     */
    public function setObjectManager($objectManager)
    {
        $this->objectManager = $objectManager;

        return $this;
    }

    /**
     *
     *
     * @return string
     */
    public function getIdentityClass()
    {
        return $this->identityClass;
    }

    /**
     *
     *
     * @param string $identityClass
     *
     * @return $this
     */
    public function setIdentityClass($identityClass)
    {
        $this->identityClass = $identityClass;

        return $this;
    }

    /**
     *
     *
     * @return ClassMetadata
     */
    public function getClassMetadata()
    {
        if (null === $this->classMetadata) {
            $this->setClassMetadata(
                $this->getObjectManager()->getClassMetadata($this->getIdentityClass())
            );
        }

        return $this->classMetadata;
    }

    /**
     *
     *
     * @param ClassMetadata $classMetadata
     *
     * @return $this
     */
    public function setClassMetadata(ClassMetadata $classMetadata)
    {
        $this->classMetadata = $classMetadata;

        return $this;
    }

    /**
     * Returns the contents of storage. Behavior is undefined when storage is empty.
     * This function assumes that the storage only contains identifier values.
     *
     * @return mixed
     */
    public function read()
    {
        $identity = parent::read();

        if (null !== $identity && !is_object($identity)) {
            $identity = $this->getObjectRepository()->find($identity);
        }

        return $identity;
    }

    /**
     * Defined by {@link StorageInterface}.
     *
     * @param mixed $identity
     *
     * @return void
     */
    public function write($identity)
    {
        if (is_object($identity)) {
            if (is_subclass_of($identity, $this->getIdentityClass())) {
                $identity = $this->getClassMetadata()->getIdentifierValues($identity);
            } elseif ($identity instanceof EntityInterface) {
                $identity = $identity->getId();
            }
        }

        parent::write($identity);
    }
}
