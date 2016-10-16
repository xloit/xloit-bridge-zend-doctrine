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

namespace Xloit\Bridge\Zend\Form;

use DoctrineModule\Persistence\ProvidesObjectManager;
use Zend\Form\ElementInterface;

/**
 * A {@link FormDoctrine} class.
 *
 * @package Xloit\Bridge\Zend\Form
 */
class FormDoctrine extends Form
{
    use ProvidesObjectManager;

    /**
     *
     *
     * @var array
     */
    private $skippedObjectManager = [];

    /**
     * Ensures state is ready for use Marshals the input filter, to ensure validation error messages are available,
     * and prepares any elements and/or fieldset that require preparation.
     *
     * @return static
     * @throws \Zend\Form\Exception\InvalidArgumentException
     */
    public function prepare()
    {
        if ($this->isPrepared) {
            return $this;
        }

        $this->prepareObjectManager();

        return parent::prepare();
    }

    /**
     * Ensures state is ready for use.
     *
     * @return void
     * @throws \Zend\Form\Exception\InvalidArgumentException
     */
    public function prepareObjectManager()
    {
        $objectManager = $this->getObjectManager();

        foreach ($this->elements as $name => $element) {
            if (array_key_exists($name, $this->skippedObjectManager)
                || !($element instanceof Element\DoctrineObjectInterface)
            ) {
                continue;
            }

            /** @var ElementInterface $element */
            if (!$element->getOption('object_manager')) {
                $this->get($name)->setOption('object_manager', $objectManager);
            }

            $this->skippedObjectManager[$name] = true;
        }
    }

    /**
     *
     *
     * @param mixed $data
     * @param bool  $onlyBase
     *
     * @throws \Zend\Form\Exception\InvalidArgumentException
     */
    public function populateValues($data, $onlyBase = false)
    {
        if (!$this->isPrepared) {
            $this->prepareObjectManager();
        }

        parent::populateValues($data, $onlyBase);
    }
}
