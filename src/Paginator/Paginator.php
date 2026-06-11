<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class Paginator extends BasePaginator implements PaginatorInterface
{
    protected array $ids = [];

    protected ?int $totalCount = null;

    protected string $idField = 'id';

    protected ?QueryBuilder $countQuery = null;

    protected ?QueryBuilder $idsQuery = null;

    protected ?QueryBuilder $query = null;

    /**
     * @var QueryBuilder[]
     */
    protected array $additionalInfoQueries = [];

    /**
     * @return $this
     */
    public function setIdField(string $field = 'id'): PaginatorInterface
    {
        $this->idField = $field;

        return $this;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getCount(): int
    {
        if (is_null($this->totalCount)) {
            $this->totalCount = $this->countQuery->getQuery()->getSingleScalarResult();
        }

        return $this->totalCount;
    }

    public function getRecords(): array
    {
        $idField = $this->idField;
        $this->ids = array_map(fn ($el) => $el[$idField], $this->getIds());

        if (0 == count($this->ids)) {
            return [];
        }

        $this->query->andWhere($this->query->expr()->in($this->query->getRootAliases()[0] . '.' . $idField, ':ids'))
            ->orderBy('FIELD(' . $this->query->getRootAliases()[0] . '.' . $idField . ', :ids)')
            ->setParameter('ids', $this->ids);

        $records = $this->query->getQuery()->getArrayResult();

        foreach ($this->additionalInfoQueries as $qb) {
            $alias = $qb->getRootAliases()[0];
            $qb->andWhere($qb->expr()->in($alias . '.' . $idField, ':ids'))
                ->setParameter('ids', $this->ids);

            $additionalInfo = $qb->getQuery()->getArrayResult();
            $records = $this->arrayCombine($records, $additionalInfo);
        }

        return $records;
    }

    /**
     * @return $this
     */
    public function setCountQuery(QueryBuilder $countQuery): PaginatorInterface
    {
        $this->countQuery = $countQuery;

        return $this;
    }

    public function getCountQuery(): QueryBuilder
    {
        return $this->countQuery;
    }

    public function getIds(): array
    {
        if ($this->maxResults && self::ALL_PARAMETER !== strtolower($this->maxResults)) {
            $this->idsQuery->setMaxResults($this->maxResults)->setFirstResult($this->getFirstResult());
        }

        $sorting = $this->getSorting();

        if (!empty($sorting)) {
            foreach ($sorting as $sort) {
                $this->idsQuery->addOrderBy($sort->getField(), $sort->getDirection());
            }
        } else {
            $this->idsQuery->addOrderBy($this->idsQuery->getRootAliases()[0] . '.' . $this->idField, 'asc');
        }

        return $this->idsQuery->getQuery()->getArrayResult();
    }

    /**
     * @return $this
     */
    public function setIdsQuery(QueryBuilder $idsQuery): PaginatorInterface
    {
        $this->idsQuery = $idsQuery;

        return $this;
    }

    /**
     * @return $this
     */
    public function setQuery(QueryBuilder $query): PaginatorInterface
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAdditionalQuery(QueryBuilder $additionalQuery): PaginatorInterface
    {
        $this->additionalInfoQueries = [$additionalQuery];

        return $this;
    }

    /**
     * @return $this
     */
    public function addAdditionalQuery(QueryBuilder $additionalQuery): PaginatorInterface
    {
        $this->additionalInfoQueries[] = $additionalQuery;

        return $this;
    }

    public function getSelectedIds(): array
    {
        return $this->ids;
    }

    public function reset(): void
    {
        $this->totalCount = null;
    }

    // ###################################################
    //                  Method Helpers                  #
    // ###################################################

    protected function arrayCombine(array $targets, array $additions): array
    {
        $tmp = [];
        foreach ($targets as $target) {
            foreach ($additions as $addition) {
                if ($target[$this->idField] == $addition[$this->idField]) {
                    $tmp[] = array_merge($target, $addition);

                    break;
                }
            }
        }

        return $tmp;
    }
}
