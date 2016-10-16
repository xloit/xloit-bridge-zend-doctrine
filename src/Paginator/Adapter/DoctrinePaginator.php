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

namespace Xloit\Bridge\Zend\Paginator\Adapter;

use Doctrine\ORM\QueryBuilder;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as AbstractDoctrinePaginator;
use Xloit\Bridge\Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * A {@link DoctrinePaginator} class
 *
 * @package Xloit\Bridge\Zend\Paginator\Adapter
 *
 * @method Paginator getPaginator()
 */
class DoctrinePaginator extends AbstractDoctrinePaginator
{
    /**
     * Constructor to prevent {@link DoctrinePaginator} from being loaded more than once.
     *
     * @param Paginator $paginator
     */
    public function __construct(Paginator $paginator)
    {
        parent::__construct($paginator);
    }

    /**
     *
     *
     * @param  Paginator $paginator
     *
     * @return static
     */
    public function setPaginator(Paginator $paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     *
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->getPaginator()->getQueryBuilder();
    }
}
