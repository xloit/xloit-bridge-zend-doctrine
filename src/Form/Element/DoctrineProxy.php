<?php
/**
 * This source file is part of Virtupeer project.
 *
 * @link      https://virtupeer.com
 * @copyright Copyright (c) 2016, Virtupeer. All rights reserved.
 */

namespace Xloit\Bridge\Zend\Form\Element;

use DoctrineModule\Form\Element\Proxy;
use Traversable;

/**
 * A {@link DoctrineProxy} class.
 *
 * @package Xloit\Bridge\Zend\Form\Element
 */
class DoctrineProxy extends Proxy
{
    /**
     * Sets the value of Objects.
     *
     * @param array|Traversable $objects
     *
     * @return static
     */
    public function setObjects($objects)
    {
        $this->objects = $objects;

        return $this;
    }
}
