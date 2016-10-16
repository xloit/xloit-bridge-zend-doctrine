<?php
/**
 * This source file is part of Virtupeer project.
 *
 * @link      https://virtupeer.com
 * @copyright Copyright (c) 2016, Virtupeer. All rights reserved.
 */

namespace Xloit\Bridge\Zend\ServiceManager;

use Interop\Container\ContainerInterface;
use Zend\Filter\FilterChain;
use Zend\Filter\FilterInterface;

/**
 * A {@link DoctrineRepositoryAbstractServiceFactory} class.
 *
 * @package Xloit\Bridge\Zend\ServiceManager
 */
class DoctrineRepositoryAbstractServiceFactory extends AbstractServiceFactory
{
    /**
     * Holds the object Manager Name
     *
     * @var string
     */
    protected $objectManagerName = 'doctrine.entitymanager.orm_default';

    /**
     * Holds the object Manager instance
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $objectManager;

    /**
     * Holds the namespace value
     *
     * @var string
     */
    protected $namespace = 'Xloit';

    /**
     * Holds the prefixServiceName value
     *
     * @var string
     */
    protected $prefixServiceName = 'Xloit';

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
        $namespace = null,
        $prefixServiceName = null,
        $objectManagerName = null,
        $pattern = "/^PREFIX_SERVICE_NAME\.doctrine\.repository\.(?P<entityName>[a-zA-Z0-9_\.]+)$/"
    ) {
        if (null !== $objectManagerName) {
            $this->objectManagerName = $objectManagerName;
        }

        if (null !== $namespace) {
            $this->prefixServiceName = $namespace;
        }

        $this->filter = new FilterChain(
            [
                'filters' => [
                    [
                        'name'     => 'Zend\Filter\Word\SeparatorToSeparator',
                        'priority' => 1,
                        'options'  => [
                            'search_separator'      => ' ',
                            'replacement_separator' => '\\'
                        ]
                    ],
                    [
                        'name'     => 'Zend\Filter\Word\DashToCamelCase',
                        'priority' => 2
                    ],
                    [
                        'name'     => 'Zend\Filter\FilterChain',
                        'priority' => 3,
                        'options'  => [
                            'filters' => [
                                [
                                    'name'     => 'Zend\Filter\UpperCaseWords',
                                    'priority' => 1
                                ],
                                [
                                    'name'     => 'Zend\Filter\Word\SeparatorToSeparator',
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
                        'name'     => 'Zend\Filter\Word\CamelCaseToDash',
                        'priority' => 4
                    ]
                ]
            ]
        );

        parent::__construct($prefixServiceName, $pattern);
    }

    /**
     * Returns the Filter value
     *
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Sets the Filter value
     *
     * @param FilterInterface $filter
     *
     * @return static
     */
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Returns the ObjectManagerName value
     *
     * @return string
     */
    public function getObjectManagerName()
    {
        return $this->objectManagerName;
    }

    /**
     * Sets the ObjectManagerName value
     *
     * @param string $objectManagerName
     *
     * @return static
     */
    public function setObjectManagerName($objectManagerName)
    {
        $this->objectManagerName = $objectManagerName;

        return $this;
    }

    /**
     * Create an object.
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     *
     * @return mixed
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \Xloit\Bridge\Zend\ServiceManager\Exception\ServiceNotFoundException
     * @throws \Xloit\Std\Exception\RuntimeException
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
     * @return boolean|array
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \Xloit\Std\Exception\RuntimeException
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
