<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * Interface PaginatorInterface
 * @package NetBull\CoreBundle\Paginator
 */
interface PaginatorInterface
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
     * @param string $field
     * @return $this
     */
    public function setIdField(string $field = 'id'): PaginatorInterface;

    /**
     * @param QueryBuilder $countQuery
     * @return $this
     */
    public function setCountQuery(QueryBuilder $countQuery): PaginatorInterface;

    /**
     * @return QueryBuilder|null
     */
    public function getCountQuery(): ?QueryBuilder;

    /**
     * @param QueryBuilder $idsQuery
     * @return $this
     */
    public function setIdsQuery(QueryBuilder $idsQuery): PaginatorInterface;

    /**
     * @return array
     */
    public function getIds(): array;

    /**
     * @param QueryBuilder $query
     * @return $this
     */
    public function setQuery(QueryBuilder $query): PaginatorInterface;

    /**
     * @param QueryBuilder $additionalQuery
     * @return $this
     */
    public function setAdditionalQuery(QueryBuilder $additionalQuery): PaginatorInterface;

    /**
     * @return array
     */
    public function getSelectedIds(): array;
}
