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

namespace Xloit\Bridge\Zend\Form\Annotation;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use DoctrineORMModule\Form\Annotation\ElementAnnotationsListener;
use Zend\EventManager\EventInterface;
use Zend\Form\FormElementManager\FormElementManagerV2Polyfill as FormElementManager;
use Zend\Validator\Callback;

/**
 * A {@link DoctrineElementAnnotationsListener} class.
 *
 * @package Xloit\Bridge\Zend\Form\Annotation
 */
class DoctrineElementAnnotationsListener extends ElementAnnotationsListener
{
    /**
     * Holds the formElementManager value.
     *
     * @var FormElementManager
     */
    protected $formElementManager;

    /**
     * Constructor to prevent {@link DoctrineElementAnnotationsListener} from being loaded more than once.
     *
     * @param ObjectManager      $objectManager
     * @param FormElementManager $formElementManager
     */
    public function __construct(ObjectManager $objectManager, FormElementManager $formElementManager)
    {
        parent::__construct($objectManager);

        $this->formElementManager = $formElementManager;
    }

    /**
     * Returns the FormElementManager value.
     *
     * @return FormElementManager
     */
    public function getFormElementManager()
    {
        return $this->formElementManager;
    }

    /**
     * Sets the FormElementManager value.
     *
     * @param FormElementManager $formElementManager
     *
     * @return $this
     */
    public function setFormElementManager($formElementManager)
    {
        $this->formElementManager = $formElementManager;

        return $this;
    }

    /**
     * Exclude GENERATOR_TYPE_IDENTITY && GENERATOR_TYPE_CUSTOM.
     * Because most of the time they are custom auto-increment.
     *
     * @internal
     *
     * @param  EventInterface $event
     *
     * @return bool
     */
    public function handleExcludeField(EventInterface $event)
    {
        /** @var ClassMetadataInfo $metadata */
        $metadata    = $event->getParam('metadata');
        $identifiers = $metadata->getIdentifierFieldNames();

        return in_array($event->getParam('name'), $identifiers, true)
               && ($metadata->generatorType === ClassMetadata::GENERATOR_TYPE_IDENTITY
                   || $metadata->generatorType === ClassMetadata::GENERATOR_TYPE_CUSTOM);
    }

    /**
     *
     *
     * @internal
     *
     * @param EventInterface $event
     *
     * @return void
     */
    public function handleFilterField(EventInterface $event)
    {
        $metadata = $event->getParam('metadata');

        if (!$metadata || !$metadata->hasField($event->getParam('name'))) {
            return;
        }

        $this->prepareEvent($event);

        $inputSpec = $event->getParam('inputSpec');

        switch ($metadata->getTypeOfField($event->getParam('name'))) {
            case 'bool':
            case 'boolean':
                $inputSpec['filters'][] = ['name' => 'Boolean'];
                break;
            case 'bigint':
            case 'integer':
            case 'smallint':
                // empty string return null, '0' is a valid number and we don't want an empty field to return 0
                $inputSpec['filters'][] = ['name' => 'StringTrim'];
                $inputSpec['filters'][] = [
                    'name'    => 'Null',
                    'options' => [
                        'type' => 'string'
                    ]
                ];
                break;
            case 'date':
                // grab the filters of the Date Element
                $inputSpecifications = $this->formElementManager->get('Date')->getInputSpecification();

                if (array_key_exists('filters', $inputSpecifications)) {
                    /** @var array $filters */
                    $filters = $inputSpecifications['filters'];

                    foreach ($filters as $filter) {
                        $inputSpec['filters'][] = $filter;
                    }
                }
                break;
            case 'datetime':
                // grab the filters of the DateTime Element
                $inputSpecifications = $this->formElementManager->get('DateTime')->getInputSpecification();

                if (array_key_exists('filters', $inputSpecifications)) {
                    /** @var array $filters */
                    $filters = $inputSpecifications['filters'];

                    foreach ($filters as $filter) {
                        $inputSpec['filters'][] = $filter;
                    }
                }
                break;
            case 'time':
                // grab the filters of the Time Element
                $inputSpecifications = $this->formElementManager->get('Time')->getInputSpecification();

                if (array_key_exists('filters', $inputSpecifications)) {
                    /** @var array $filters */
                    $filters = $inputSpecifications['filters'];

                    foreach ($filters as $filter) {
                        $inputSpec['filters'][] = $filter;
                    }
                }
                break;
            case 'datetimetz':
                $inputSpec['filters'][] = ['name' => 'StringTrim'];
                break;
            case 'string':
            case 'text':
                // empty string return null, but we allow '0'
                $inputSpec['filters'][] = ['name' => 'StringTrim'];
                $inputSpec['filters'][] = [
                    'name'    => 'Null',
                    'options' => [
                        'type' => 'string'
                    ]
                ];
        }

        $event->setParam('inputSpec', $inputSpec);
    }

    /**
     *
     *
     * @param EventInterface $event
     *
     * @return void
     */
    public function handleValidatorField(EventInterface $event)
    {
        $mapping  = $this->getFieldMapping($event);
        $metadata = $event->getParam('metadata');

        if (!$mapping) {
            return;
        }

        $this->prepareEvent($event);

        $inputSpec = $event->getParam('inputSpec');

        switch ($metadata->getTypeOfField($event->getParam('name'))) {
            case 'bool':
            case 'boolean':
                $inputSpec['validators'][] = [
                    'name'    => 'InArray',
                    'options' => [
                        'haystack' => [
                            '0',
                            '1'
                        ]
                    ]
                ];
                break;
            case 'float':
                $inputSpec['validators'][] = ['name' => 'Float'];
                break;
            case 'bigint':
            case 'integer':
            case 'smallint':
                $inputSpec['validators'][] = ['name' => 'Digits'];
                break;
            case 'string':
                // here we provide a callback, because StringLength validator shocks with null values
                if (array_key_exists('length', $mapping)) {
                    $inputSpec['validators'][] = [
                        'name'    => 'Callback',
                        'options' => [
                            'callback'        => [
                                $this,
                                'stringLengthValidatorCallback'
                            ],
                            'callbackOptions' => [
                                [
                                    'length' => $mapping['length']
                                ]
                            ],
                            'messages'        => [
                                Callback::INVALID_VALUE => 'Maximum allowed text size exceeded'
                            ]
                        ]
                    ];
                }
                break;
            case 'date':
                // grab the validators of the Date Element
                $inputSpecifications = $this->formElementManager->get('Date')->getInputSpecification();

                if (array_key_exists('validators', $inputSpecifications)) {
                    /** @var array $validators */
                    $validators = $inputSpecifications['validators'];

                    foreach ($validators as $validator) {
                        $inputSpec['validators'][] = $validator;
                    }
                }
                break;
            case 'datetime':
                // grab the validators of the DateTime Element
                $inputSpecifications = $this->formElementManager->get('DateTime')->getInputSpecification();

                if (array_key_exists('validators', $inputSpecifications)) {
                    /** @var array $validators */
                    $validators = $inputSpecifications['validators'];

                    foreach ($validators as $validator) {
                        $inputSpec['validators'][] = $validator;
                    }
                }
                break;
            case 'time':
                // grab the validators of the Time Element
                $inputSpecifications = $this->formElementManager->get('Time')->getInputSpecification();

                if (array_key_exists('validators', $inputSpecifications)) {
                    /** @var array $validators */
                    $validators = $inputSpecifications['validators'];

                    foreach ($validators as $validator) {
                        $inputSpec['validators'][] = $validator;
                    }
                }
                break;
        }

        $event->setParam('inputSpec', $inputSpec);
    }

    /**
     *
     *
     * @param string $value
     * @param array  $options
     *
     * @return bool
     */
    public function stringLengthValidatorCallback($value, $options)
    {
        $maximumLength = array_key_exists('length', $options) ? $options['length'] : 0;

        return strlen($value) < $maximumLength;
    }
}
