<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * Interface PaginatorSimpleInterface
 * @package NetBull\CoreBundle\Paginator
 */
interface PaginatorSimpleInterface
{
    /**
     * @return int|mixed
     * @throws NonUniqueResultException
     */
    public function getCount();

    /**
     * @return array
     */
    public function getRecords(): array;

    /**
     * Handle the pagination
     * @return array
     */
    public function paginate(): array;

    /**
     * @param array $ids
     * @return $this
     */
    public function setIds(array $ids): PaginatorSimpleInterface;

    /**
     * @return array
     */
    public function getIds(): array;

    /**
     * @param QueryBuilder $query
     * @return $this
     */
    public function setQuery(QueryBuilder $query): PaginatorSimpleInterface;
}
