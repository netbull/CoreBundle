<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\QueryBuilder;

interface PaginatorRepositoryInterface
{
    /**
     * @return mixed
     */
    public function getPaginationCount(array $params = []): QueryBuilder;

    public function getPaginationIds(array $params = []): QueryBuilder;

    public function getPaginationQuery(array $params = []): QueryBuilder;
}
