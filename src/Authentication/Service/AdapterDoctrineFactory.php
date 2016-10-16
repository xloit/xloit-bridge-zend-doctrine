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
use Xloit\Bridge\Zend\Authentication\Adapter\Doctrine as DoctrineAdapter;
use Xloit\Bridge\Zend\ServiceManager\AbstractFactory;

/**
 * An {@link AdapterDoctrineFactory} class
 *
 * @package Xloit\Bridge\Zend\Authentication\Service
 */
class AdapterDoctrineFactory extends AbstractFactory
{
    /**
     * Create the instance service (v3)
     *
     * @param ContainerInterface $container
     * @param string             $name
     * @param array              $options
     *
     * @return DoctrineAdapter
     * @throws \Xloit\Bridge\Zend\ServiceManager\Exception\StateException
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Xloit\Std\Exception\RuntimeException
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new DoctrineAdapter($this->getOption('options'));
    }
}
