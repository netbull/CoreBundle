<?php

namespace NetBull\CoreBundle\Paginator;

interface PaginatorRepositoryInterface
{
    /**
     * Build Count Query
     * @param array $params
     * @return mixed
     */
    public function getPaginationCount(array $params = []);

    /**
     * Build Query to get the Ids
     * @param array $params
     * @return mixed
     */
    public function getPaginationIds(array $params = []);

    /**
     * Main Query
     * @param array $params
     * @return mixed
     */
    public function getPaginationQuery(array $params = []);
}
