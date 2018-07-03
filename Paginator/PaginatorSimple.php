<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class PaginatorSimple
 * @package NetBull\CoreBundle\Paginator
 */
class PaginatorSimple extends BasePaginator
{
    /**
     * @var array
     */
    protected $ids = [];

    /**
     * @var QueryBuilder|null
     */
    protected $query = null;

    /**
     * Paginator constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        parent::__construct($requestStack);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCount()
    {
        return count($this->ids);
    }

    /**
     * @inheritdoc
     */
    public function getRecords()
    {
        if (count($this->ids) == 0) {
            return [];
        }

        $ids = array_slice($this->ids, $this->getFirstResult(), $this->maxResults);

        $this->query->andWhere($this->query->expr()->in($this->query->getRootAliases()[0] . '.id', ':ids'))->setParameter('ids', $ids);

        $this->query->orderBy(sprintf('FIELD(%s, %s)', $this->query->getRootAliases()[0] . '.id', implode(',', $ids)));

        return $this->query->getQuery()->getArrayResult();
    }

    /**
     * @return null
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function setIds(array $ids)
    {
        $this->ids = array_map(function ($el) { return $el['id']; }, $ids);

        return $this;
    }

    /**
     * @param QueryBuilder $query
     * @return $this
     */
    public function setQuery(QueryBuilder $query)
    {
        $this->query = $query;

        return $this;
    }
}
