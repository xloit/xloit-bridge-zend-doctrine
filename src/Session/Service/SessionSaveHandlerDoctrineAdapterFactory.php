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

namespace Xloit\Bridge\Zend\Session\Service;

use Interop\Container\ContainerInterface;
use Xloit\Bridge\Zend\ServiceManager\AbstractFactory;
use Xloit\Bridge\Zend\Session\SaveHandler\Database\Adapter\Doctrine;

/**
 * A {@link SessionSaveHandlerDoctrineAdapterFactory} class.
 *
 * @package Xloit\Bridge\Zend\Session\Service
 */
class SessionSaveHandlerDoctrineAdapterFactory extends AbstractFactory
{
    /**
     * Create the instance service (v3).
     *
     * @param ContainerInterface $container
     * @param string             $name
     * @param null|array         $options
     *
     * @return Doctrine
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Xloit\Bridge\Zend\ServiceManager\Exception\StateException
     * @throws \Xloit\Std\Exception\RuntimeException
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $storageManager = $this->getOption('storageManager');
        $options        = $options ?: $this->getOption('options');

        return new Doctrine($storageManager, $options);
    }
}
