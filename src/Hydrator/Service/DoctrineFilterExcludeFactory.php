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

namespace Xloit\Bridge\Zend\Hydrator\Service;

use DoctrineModule\Stdlib\Hydrator\Filter\PropertyName;
use Interop\Container\ContainerInterface;
use Xloit\Bridge\Zend\ServiceManager\AbstractFactory;

/**
 * A {@link DoctrineFilterExcludeFactory} class.
 *
 * @package Xloit\Bridge\Zend\Hydrator\Service
 */
class DoctrineFilterExcludeFactory extends AbstractFactory
{
    /**
     * Create the instance service (v3).
     *
     * @param ContainerInterface $container
     * @param string             $name
     * @param null|array         $options
     *
     * @return PropertyName
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Xloit\Bridge\Zend\ServiceManager\Exception\StateException
     * @throws \Xloit\Std\Exception\RuntimeException
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $properties = $this->getOption('properties', false);
        $exclude    = true;

        if ($this->hasOption('exclude')) {
            $exclude = $this->getOption('exclude', false);
        }

        return new PropertyName($properties, $exclude);
    }
}
