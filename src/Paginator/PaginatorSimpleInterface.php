<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface PaginatorSimpleInterface
 * @package NetBull\CoreBundle\Paginator
 */
interface PaginatorSimpleInterface
{
    /**
     * @return int|mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCount();

    /**
     * @return array
     */
    public function getRecords();

    /**
     * Handle the pagination
     * @return array
     */
    public function paginate();

    /**
     * @param array $ids
     * @return $this
     */
    public function setIds(array $ids);

    /**
     * @return array
     */
    public function getIds();

    /**
     * @param QueryBuilder $query
     * @return $this
     */
    public function setQuery(QueryBuilder $query);
}
