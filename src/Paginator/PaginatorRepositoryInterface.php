<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\QueryBuilder;

interface PaginatorRepositoryInterface
{
    /**
     * @param array $params
     * @return mixed
     */
    public function getPaginationCount(array $params = []): QueryBuilder;

    /**
     * @param array $params
     * @return QueryBuilder
     */
    public function getPaginationIds(array $params = []): QueryBuilder;

    /**
     * @param array $params
     * @return QueryBuilder
     */
    public function getPaginationQuery(array $params = []): QueryBuilder;
}
