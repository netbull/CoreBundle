<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\QueryBuilder;

class PaginatorSimple extends BasePaginator implements PaginatorSimpleInterface
{
    /**
     * @var array
     */
    protected array $ids = [];

    /**
     * @var QueryBuilder|null
     */
    protected ?QueryBuilder $query = null;

    /**
     * @return int
     */
    public function getCount(): int
    {
        return count($this->ids);
    }

    /**
     * @return array
     */
    public function getRecords(): array
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
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function setIds(array $ids): PaginatorSimpleInterface
    {
        $this->ids = array_map(function ($el) { return $el['id']; }, $ids);

        return $this;
    }

    /**
     * @param QueryBuilder $query
     * @return $this
     */
    public function setQuery(QueryBuilder $query): PaginatorSimpleInterface
    {
        $this->query = $query;

        return $this;
    }
}
