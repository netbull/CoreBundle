<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

interface PaginatorInterface
{
    /**
     * @throws NonUniqueResultException
     */
    public function getCount(): int;

    public function getRecords(): array;

    /**
     * Handle the pagination
     */
    public function paginate(): array;

    /**
     * @return $this
     */
    public function setIdField(string $field = 'id'): PaginatorInterface;

    /**
     * @return $this
     */
    public function setCountQuery(QueryBuilder $countQuery): PaginatorInterface;

    public function getCountQuery(): ?QueryBuilder;

    /**
     * @return $this
     */
    public function setIdsQuery(QueryBuilder $idsQuery): PaginatorInterface;

    public function getIds(): array;

    /**
     * @return $this
     */
    public function setQuery(QueryBuilder $query): PaginatorInterface;

    /**
     * @return $this
     */
    public function setAdditionalQuery(QueryBuilder $additionalQuery): PaginatorInterface;

    public function getSelectedIds(): array;
}
