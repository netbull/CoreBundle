<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * Class Paginator
 * @package NetBull\CoreBundle\Paginator
 */
class Paginator extends BasePaginator implements PaginatorInterface
{
    /**
     * @var array
     */
    protected $ids = [];

    /**
     * @var int|null
     */
    protected ?int $totalCount = null;

    /**
     * @var string
     */
    protected $idField = 'id';

    /**
     * @var QueryBuilder|null
     */
    protected $countQuery = null;

    /**
     * @var QueryBuilder|null
     */
    protected $idsQuery = null;

    /**
     * @var QueryBuilder|null
     */
    protected $query = null;

    /**
     * @var QueryBuilder|null
     */
    protected $additionalInfoQuery = null;

    /**
     * @param string $field
     * @return $this
     */
    public function setIdField(string $field = 'id'): PaginatorInterface
    {
        $this->idField = $field;

        return $this;
    }

    /**
     * @return int|mixed
     * @throws NonUniqueResultException
     */
    public function getCount(): int
    {
        if (is_null($this->totalCount)) {
            $this->totalCount = $this->countQuery->getQuery()->getSingleScalarResult();
        }

        return $this->totalCount;
    }

    /**
     * @inheritdoc
     */
    public function getRecords(): array
    {
        $idField = $this->idField;
        $this->ids = array_map(function ($el) use ($idField) { return $el[$idField]; }, $this->getIds());

        if (count($this->ids) == 0) {
            return [];
        }

        $this->query->andWhere($this->query->expr()->in($this->query->getRootAliases()[0] . '.' . $idField, ':ids'))
            ->orderBy('FIELD(' . $this->query->getRootAliases()[0] . '.' . $idField . ', :ids)')
            ->setParameter('ids', $this->ids);

        $records = $this->query->getQuery()->getArrayResult();

        if ($this->additionalInfoQuery) {
            $qb = $this->additionalInfoQuery;
            $alias = $qb->getRootAliases()[0];
            $qb->andWhere($qb->expr()->in($alias . '.' . $idField, ':ids'))
                ->orderBy('FIELD(' . $alias . '.' . $idField . ', :ids)')
                ->setParameter('ids', $this->ids);

            $additionalInfo = $this->additionalInfoQuery->getQuery()->getArrayResult();
            $records = $this->arrayCombine($records, $additionalInfo);
        }

        return $records;
    }

    /**
     * @param QueryBuilder $countQuery
     * @return $this
     */
    public function setCountQuery(QueryBuilder $countQuery): PaginatorInterface
    {
        $this->countQuery = $countQuery;

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function getCountQuery(): QueryBuilder
    {
        return $this->countQuery;
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        if ($this->maxResults && strtolower($this->maxResults) !== self::ALL_PARAMETER) {
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
     * @param QueryBuilder $idsQuery
     * @return $this
     */
    public function setIdsQuery(QueryBuilder $idsQuery): PaginatorInterface
    {
        $this->idsQuery = $idsQuery;

        return $this;
    }

    /**
     * @param QueryBuilder $query
     * @return $this
     */
    public function setQuery(QueryBuilder $query): PaginatorInterface
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param QueryBuilder $additionalQuery
     * @return $this
     */
    public function setAdditionalQuery(QueryBuilder $additionalQuery): PaginatorInterface
    {
        $this->additionalInfoQuery = $additionalQuery;

        return $this;
    }

    /**
     * @return array
     */
    public function getSelectedIds(): array
    {
        return $this->ids;
    }

    ####################################################
    #                  Method Helpers                  #
    ####################################################

    /**
     * @param array $targets
     * @param array $additions
     * @return array
     */
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
