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
use DoctrineORMModule\Form\Annotation\AnnotationBuilder as BaseDoctrineAnnotationBuilder;
use Zend\EventManager\EventManagerInterface;
use Zend\Form\Factory;
use Zend\Form\FormElementManager\FormElementManagerV2Polyfill as FormElementManager;

/**
 * A {@link DoctrineAnnotationBuilder} class
 *
 * @package Xloit\Bridge\Zend\Form\Annotation
 */
class DoctrineAnnotationBuilder extends BaseDoctrineAnnotationBuilder
{
    /**
     * Holds the formElementManager value
     *
     * @var FormElementManager
     */
    protected $formElementManager;

    /**
     * Constructor to prevent {@link DoctrineAnnotationBuilder} from being loaded more than once.
     *
     * @param ObjectManager      $objectManager
     * @param FormElementManager $formElementManager
     */
    public function __construct(ObjectManager $objectManager, FormElementManager $formElementManager)
    {
        parent::__construct($objectManager);

        $this->formElementManager = $formElementManager;
        // We set the FEM as form factory so the ZF2 AnnotationBuilder is aware of custom form elements names
        $this->formFactory = new Factory($this->formElementManager);
    }

    /**
     * Returns the FormElementManager value
     *
     * @return FormElementManager
     */
    public function getFormElementManager()
    {
        return $this->formElementManager;
    }

    /**
     * Sets the FormElementManager value
     *
     * @param FormElementManager $formElementManager
     *
     * @return static
     */
    public function setFormElementManager($formElementManager)
    {
        $this->formElementManager = $formElementManager;

        return $this;
    }

    /**
     *
     *
     * @param EventManagerInterface $events
     *
     * @return static
     */
    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($events);

        (new DoctrineElementAnnotationsListener($this->objectManager, $this->formElementManager))
            ->attach($this->getEventManager());

        return $this;
    }
}
