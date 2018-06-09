<?php

namespace NetBull\CoreBundle\Paginator;

/**
 * Interface PaginatorInterface
 * @package NetBull\CoreBundle\Paginator
 */
interface PaginatorInterface
{
    /**
     * Build Count Query
     * @return mixed
     */
    public function getPaginationCount();

    /**
     * Build Query to get the Ids
     * @return mixed
     */
    public function getPaginationIds();

    /**
     * Main Query
     * @return mixed
     */
    public function getPaginationQuery();
}
