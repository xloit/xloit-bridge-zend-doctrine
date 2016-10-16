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

namespace Xloit\Bridge\Zend\Authentication\Service;

use Interop\Container\ContainerInterface;
use Xloit\Bridge\Zend\Authentication\Storage\DoctrineSession;

/**
 * A {@link StorageDoctrineSessionFactory} class
 *
 * @package Xloit\Bridge\Zend\Authentication\Service
 */
class StorageDoctrineSessionFactory extends StorageSessionFactory
{
    /**
     * Create the instance service (v3)
     *
     * @param ContainerInterface $container
     * @param string             $name
     * @param array              $options
     *
     * @return DoctrineSession
     * @throws \Zend\Session\Exception\InvalidArgumentException
     * @throws \Xloit\Bridge\Zend\ServiceManager\Exception\StateException
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Xloit\Std\Exception\RuntimeException
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $constructorConfig = $this->getConstructorConfig($container);
        $storage           = new DoctrineSession(
            $constructorConfig['namespace'], $constructorConfig['member'], $constructorConfig['sessionManager']
        );

        if ($this->hasOption('objectManager')) {
            $storage->setObjectManager($this->getOption('objectManager'));
        }

        if ($this->hasOption('identityClass')) {
            $storage->setIdentityClass($this->getOption('identityClass', false));
        }

        return $storage;
    }
}
