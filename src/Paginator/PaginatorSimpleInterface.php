<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

interface PaginatorSimpleInterface
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
    public function setIds(array $ids): PaginatorSimpleInterface;

    public function getIds(): array;

    /**
     * @return $this
     */
    public function setQuery(QueryBuilder $query): PaginatorSimpleInterface;
}
