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

namespace Xloit\Bridge\Zend\Form\Element;

use DoctrineModule\Form\Element\ObjectSelect;
use Zend\Form\Exception\InvalidArgumentException;

/**
 * A {@link DoctrineSelect} class
 *
 * @package Xloit\Bridge\Zend\Form\Element
 */
class DoctrineSelect extends ObjectSelect implements DoctrineObjectInterface
{
    /**
     * Constructor to prevent {@link DoctrineSelect} from being loaded more
     * than once.
     *
     * @param int|string $name
     * @param array      $options
     *
     * @throws InvalidArgumentException
     */
    public function __construct($name = null, array $options = [])
    {
        parent::__construct(
            $name,
            array_merge(
                [
                    'empty_item_label'   => '-- Please Select --',
                    'display_empty_item' => true
                ],
                $options
            )
        );
    }

    /**
     *
     *
     * @return DoctrineProxy
     */
    public function getProxy()
    {
        if (null === $this->proxy) {
            $this->proxy = new DoctrineProxy();
        }

        return $this->proxy;
    }
}
