<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface PaginatorInterface
 * @package NetBull\CoreBundle\Paginator
 */
interface PaginatorInterface
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
     * @param string $field
     * @return $this
     */
    public function setIdField(string $field = 'id');

    /**
     * @param QueryBuilder $query
     * @return $this
     */
    public function setCountQuery(QueryBuilder $query);

    /**
     * @return QueryBuilder|null
     */
    public function getCountQuery();

    /**
     * @param QueryBuilder $query
     * @return $this
     */
    public function setIdsQuery(QueryBuilder $query);

    /**
     * @return array
     */
    public function getIds();

    /**
     * @param QueryBuilder $query
     * @return $this
     */
    public function setQuery(QueryBuilder $query);

    /**
     * @param QueryBuilder $query
     * @return $this
     */
    public function setAdditionalQuery(QueryBuilder $query);

    /**
     * @return array
     */
    public function getSelectedIds();

    /**
     * @param array $sort
     * @return $this
     */
    public function addAdditionalSorting(array $sort = []);
}
