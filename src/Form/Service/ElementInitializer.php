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

namespace Xloit\Bridge\Zend\Form\Service;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Xloit\Bridge\Zend\Form\Element\DoctrineObjectInterface;
use Xloit\Bridge\Zend\ServiceManager\AbstractFactory;
use Xloit\Bridge\Zend\ServiceManager\AbstractServiceInitializer;

/**
 * A {@link ElementInitializer} class.
 *
 * @package Xloit\Bridge\Zend\Form\Service
 */
class ElementInitializer extends AbstractServiceInitializer
{
    /**
     * Initialize the given instance.
     *
     * @param  ContainerInterface $container
     * @param  mixed              $instance
     *
     * @return void
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        if (!is_object($instance)) {
            return;
        }

        $awareInterface    = $this->getAwareInstanceInterface();
        $instanceInterface = $this->getInstanceInterface();
        $hashInstance      = get_class($instance) . '@' . spl_object_hash($instance);

        // TODO: Benchmark, please
        if ($instance instanceof $awareInterface && !array_key_exists($hashInstance, $this->skipped)) {
            $instanceValue = null;

            // Some vendor will try to throw an exception
            try {
                $instanceValue = $instance->getOption('object_manager');
            } catch (\Exception $exception) {
            }

            // Maybe it was initialized by factory
            if ($instanceValue && is_string($instanceInterface) && $instanceValue instanceof $instanceInterface) {
                $this->skipped[$hashInstance] = true;

                return;
            }

            if ($this->peerContainer === null) {
                $this->peerContainer = AbstractFactory::getPeerContainer($container);
            }

            $instance->setOption('object_manager', $this->getServiceInstance($container));
        }
    }

    /**
     *
     *
     * @return string
     */
    protected function getAwareInstanceInterface()
    {
        return DoctrineObjectInterface::class;
    }

    /**
     *
     *
     * @return string
     */
    protected function getInstanceInterface()
    {
        return EntityManager::class;
    }

    /**
     *
     *
     * @return array
     */
    protected function getServiceNames()
    {
        return [
            'doctrine.entitymanager.orm_default'
        ];
    }

    /**
     *
     *
     * @return array
     */
    protected function getMethods()
    {
        return [
        ];
    }
}
