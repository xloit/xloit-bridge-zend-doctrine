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

namespace Xloit\Bridge\Zend\ServiceManager;

use Interop\Container\ContainerInterface;
use Zend\Filter\FilterChain;
use Zend\Filter\FilterInterface;
use Zend\Filter\Word\SeparatorToSeparator;
use Zend\Filter\Word\DashToCamelCase;
use Zend\Filter\UpperCaseWords;
use Zend\Filter\Word\CamelCaseToDash;

/**
 * A {@link DoctrineRepositoryAbstractServiceFactory} class.
 *
 * @package Xloit\Bridge\Zend\ServiceManager
 */
class DoctrineRepositoryAbstractServiceFactory extends AbstractServiceFactory
{
    /**
     * Holds the object Manager Name.
     *
     * @var string
     */
    protected $objectManagerName = 'doctrine.entitymanager.orm_default';

    /**
     * Holds the object Manager instance.
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $objectManager;

    /**
     * Holds the prefixServiceName value.
     *
     * @var string
     */
    protected $prefixServiceName;

    /**
     * Holds the filter object
     *
     * @var FilterInterface
     */
    protected $filter;

    /**
     * Constructor to prevent {@link AbstractDoctrineRepositoryFactory} from being loaded more than once.
     *
     * @param string $namespace
     * @param string $prefixServiceName
     * @param string $objectManagerName
     * @param string $pattern
     */
    public function __construct(
        $namespace = 'Xloit',
        $prefixServiceName = 'Xloit',
        $objectManagerName = null,
        $pattern = "/^PREFIX_SERVICE_NAME\.doctrine\.repository\.(?P<entityName>[a-zA-Z0-9_\.]+)$/"
    ) {
        if (null !== $objectManagerName) {
            $this->objectManagerName = $objectManagerName;
        }

        if (null !== $prefixServiceName) {
            $this->prefixServiceName = $prefixServiceName;
        }

        $this->filter = new FilterChain(
            [
                'filters' => [
                    [
                        'name'     => SeparatorToSeparator::class,
                        'priority' => 1,
                        'options'  => [
                            'search_separator'      => ' ',
                            'replacement_separator' => '\\'
                        ]
                    ],
                    [
                        'name'     => DashToCamelCase::class,
                        'priority' => 2
                    ],
                    [
                        'name'     => FilterChain::class,
                        'priority' => 3,
                        'options'  => [
                            'filters' => [
                                [
                                    'name'     => UpperCaseWords::class,
                                    'priority' => 1
                                ],
                                [
                                    'name'     => SeparatorToSeparator::class,
                                    'priority' => 2,
                                    'options'  => [
                                        'search_separator'      => '.',
                                        'replacement_separator' => ' '
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'name'     => CamelCaseToDash::class,
                        'priority' => 4
                    ]
                ]
            ]
        );

        parent::__construct($namespace, $pattern);
    }

    /**
     * Returns the Filter value.
     *
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Sets the Filter value.
     *
     * @param FilterInterface $filter
     *
     * @return $this
     */
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Returns the ObjectManagerName value.
     *
     * @return string
     */
    public function getObjectManagerName()
    {
        return $this->objectManagerName;
    }

    /**
     * Sets the ObjectManagerName value.
     *
     * @param string $objectManagerName
     *
     * @return $this
     */
    public function setObjectManagerName($objectManagerName)
    {
        $this->objectManagerName = $objectManagerName;

        return $this;
    }

    /**
     * Create an object.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Xloit\Bridge\Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $mappings = $this->getServiceMapping($container, $requestedName);

        if (!$mappings) {
            throw new Exception\ServiceNotFoundException(
                sprintf(
                    'An alias "%s" was requested but no service could be found.',
                    $requestedName
                )
            );
        }

        /** @var array $mappings */
        if (empty($mappings['namespace']) || empty($mappings['entityName'])) {
            throw new Exception\ServiceNotFoundException(
                sprintf(
                    'An alias "%s" was requested but no service could be found.',
                    $requestedName
                )
            );
        }

        /** @var array $mappings */
        $className = $mappings['namespace'] . '\\' . $mappings['entityName'];

        /**
         * Because we are using hierarchy structure we need add the namespace based on entity name.
         *
         * e.g.
         * xloit.doctrine.repository.entity => Namespace\Entity\Entity
         */
        if (!class_exists($className)) {
            $className .= '\\' . $mappings['entityName'];
        }

        if (!class_exists($className)) {
            throw new Exception\ServiceNotFoundException(
                sprintf(
                    'An entity "%s" using alias "%s" was requested but no entity class could be found.',
                    $className,
                    $requestedName
                )
            );
        }

        if ($this->objectManager === null) {
            $this->objectManager = $container->get($this->objectManagerName);
        }

        return $this->objectManager->getRepository($className);
    }

    /**
     * Gets options from configuration based on name.
     *
     * @param ContainerInterface $container
     * @param string             $name
     *
     * @return false|array
     */
    public function getServiceMapping(ContainerInterface $container, $name)
    {
        if (array_key_exists($name, $this->lookupCache)) {
            return $this->lookupCache[$name];
        }

        $matches = [];
        $pattern = str_replace('PREFIX_SERVICE_NAME', $this->namespace, $this->pattern);

        if (!preg_match($pattern, $name, $matches)) {
            return false;
        }

        $entityName     = $this->filter->filter($matches['entityName']);
        $serviceMapping = [
            'namespace'  => trim($this->prefixServiceName, '\\'),
            'entityName' => trim($entityName, '\\')
        ];

        $this->lookupCache[$name] = $serviceMapping;

        return $serviceMapping;
    }
}
