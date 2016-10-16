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

namespace Xloit\Bridge\Zend\Authentication\Options;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * A {@link DoctrineOptions} class
 *
 * @package Xloit\Bridge\Zend\Authentication\Options
 */
class DoctrineOptions extends AuthenticationOptions
{
    /**
     * A valid object implementing ObjectRepository interface (or ObjectManager/identityClass)
     *
     * @var EntityManager
     */
    protected $objectManager;

    /**
     * A valid object implementing ObjectRepository interface (or ObjectManager/identityClass)
     *
     * @var EntityRepository
     */
    protected $objectRepository;

    /**
     * If an objectManager is not supplied, this metadata will be used by
     * {@link \Xloit\Bridge\Zend\Authentication\Adapter\Doctrine}
     *
     * @var ClassMetadata
     */
    protected $classMetadata;

    /**
     * Entity's class name
     *
     * @var string
     */
    protected $identityClass;

    /**
     *
     *
     * @param EntityManager $objectManager
     *
     * @return static
     */
    public function setObjectManager(EntityManager $objectManager)
    {
        $this->objectManager = $objectManager;

        return $this;
    }

    /**
     *
     *
     * @return EntityManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     *
     *
     * @param EntityRepository $objectRepository
     *
     * @return static
     */
    public function setObjectRepository(EntityRepository $objectRepository)
    {
        $this->objectRepository = $objectRepository;

        return $this;
    }

    /**
     *
     *
     * @return EntityRepository
     */
    public function getObjectRepository()
    {
        if (null === $this->objectRepository) {
            /** @noinspection PhpParamsInspection */
            $this->setObjectRepository(
                $this->getObjectManager()->getRepository($this->getIdentityClass())
            );
        }

        return $this->objectRepository;
    }

    /**
     *
     *
     * @param ClassMetadata $classMetadata
     *
     * @return static
     */
    public function setClassMetadata(ClassMetadata $classMetadata)
    {
        $this->classMetadata = $classMetadata;

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
            $this->setClassMetadata($this->getObjectManager()->getClassMetadata($this->getIdentityClass()));
        }

        return $this->classMetadata;
    }

    /**
     *
     *
     * @param string $identityClass
     *
     * @return static
     */
    public function setIdentityClass($identityClass)
    {
        $this->identityClass = $identityClass;

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
}
