<?php
/**
 * This source file is part of Xloit project.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the New BSD License that is bundled with this package in the file
 * LICENSE. It is also available through the world-wide-web at this URL:
 * <http://opensource.org/licenses/BSD-3-Clause>
 * If you did not receive a copy of the license and are unable to obtain it through the world-wide-web,
 * please send an email to <license@xloit.com> so we can send you a copy immediately.
 *
 * @license   BSD
 * @link      http://xloit.com
 * @copyright Copyright (c) 2017, Xloit. All rights reserved.
 */

namespace Xloit\Bridge\Zend;

use Zend\Form\ElementFactory;

/**
 * A {@link Module} class.
 *
 * @package Xloit\Bridge\Zend
 */
class Module
{
    /**
     * Return default zend-validator configuration for zend-mvc applications.
     *
     * @return array
     */
    public function getConfig()
    {
        /** @noinspection SpellCheckingInspection */
        return [
            'form_elements' => [
                'aliases'   => [
                    'doctrinemulticheckbox' => Form\Element\DoctrineMultiCheckbox::class,
                    'doctrineMulticheckbox' => Form\Element\DoctrineMultiCheckbox::class,
                    'doctrineMultiCheckbox' => Form\Element\DoctrineMultiCheckbox::class,
                    'Doctrinemulticheckbox' => Form\Element\DoctrineMultiCheckbox::class,
                    'DoctrineMulticheckbox' => Form\Element\DoctrineMultiCheckbox::class,
                    'DoctrineMultiCheckbox' => Form\Element\DoctrineMultiCheckbox::class,
                    'doctrineradio'         => Form\Element\DoctrineRadio::class,
                    'doctrineRadio'         => Form\Element\DoctrineRadio::class,
                    'Doctrineradio'         => Form\Element\DoctrineRadio::class,
                    'DoctrineRadio'         => Form\Element\DoctrineRadio::class,
                    'doctrineselect'        => Form\Element\DoctrineSelect::class,
                    'doctrineSelect'        => Form\Element\DoctrineSelect::class,
                    'Doctrineselect'        => Form\Element\DoctrineSelect::class,
                    'DoctrineSelect'        => Form\Element\DoctrineSelect::class
                ],
                'factories' => [
                    Form\Element\DoctrineMultiCheckbox::class => ElementFactory::class,
                    Form\Element\DoctrineRadio::class         => ElementFactory::class,
                    Form\Element\DoctrineSelect::class        => ElementFactory::class
                ]
            ]
        ];
    }
}
