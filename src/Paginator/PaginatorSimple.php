<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\QueryBuilder;

class PaginatorSimple extends BasePaginator implements PaginatorSimpleInterface
{
    protected array $ids = [];

    protected ?QueryBuilder $query = null;

    public function getCount(): int
    {
        return count($this->ids);
    }

    public function getRecords(): array
    {
        if (0 == count($this->ids)) {
            return [];
        }

        $ids = array_slice($this->ids, $this->getFirstResult(), $this->maxResults);

        $this->query->andWhere($this->query->expr()->in($this->query->getRootAliases()[0] . '.id', ':ids'))->setParameter('ids', $ids);

        $this->query->orderBy(sprintf('FIELD(%s, %s)', $this->query->getRootAliases()[0] . '.id', implode(',', $ids)));

        return $this->query->getQuery()->getArrayResult();
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @return $this
     */
    public function setIds(array $ids): PaginatorSimpleInterface
    {
        $this->ids = array_map(function ($el) { return $el['id']; }, $ids);

        return $this;
    }

    /**
     * @return $this
     */
    public function setQuery(QueryBuilder $query): PaginatorSimpleInterface
    {
        $this->query = $query;

        return $this;
    }
}
