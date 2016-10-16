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

namespace Xloit\Bridge\Zend\Doctrine\ORM;

use Doctrine\ORM\Query;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrinePaginatorAdapter;
use Xloit\Bridge\Doctrine\ORM\EntityQueryBuilder as BaseEntityQueryBuilder;
use Xloit\Bridge\Doctrine\ORM\EntityRepository as BaseEntityRepository;
use Zend\Paginator\Paginator;

/**
 * A {@link EntityRepository} class
 *
 * @package Xloit\Bridge\Zend\Doctrine\ORM
 */
class EntityRepository extends BaseEntityRepository
{
    /**
     *
     *
     * @param BaseEntityQueryBuilder $query               A Doctrine ORM query builder.
     * @param bool                   $fetchJoinCollection Whether the query joins a collection (true by default).
     *
     * @return Paginator
     * @throws \Zend\Paginator\Exception\InvalidArgumentException
     */
    public function getPaginator(BaseEntityQueryBuilder $query, $fetchJoinCollection = true)
    {
        $paginator = parent::getPaginator($query, $fetchJoinCollection);
        $adapter   = new DoctrinePaginatorAdapter($paginator);

        return new Paginator($adapter);
    }

    /**
     * Retrieve paginator.
     *
     * @param int $page
     * @param int $perPage
     *
     * @return Paginator
     * @throws \Xloit\Bridge\Doctrine\ORM\Exception\InvalidArgumentException
     * @throws \Zend\Paginator\Exception\InvalidArgumentException
     */
    public function paginate($page = 1, $perPage = null)
    {
        $perPage      = $perPage ?: $this->getMaxResults();
        $queryBuilder = $this->createQueryBuilder();

        $queryBuilder->paginate($page, $perPage);

        $paginator = $this->getPaginator($queryBuilder);

        $paginator->setCurrentPageNumber($page)
                  ->setItemCountPerPage($perPage);

        return $paginator;
    }
}
