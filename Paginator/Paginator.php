<?php

namespace NetBull\CoreBundle\Paginator;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Paginator
 * @package NetBull\CoreBundle\Paginator
 */
class Paginator extends BasePaginator
{
    /**
     * @var array
     */
    protected $ids = [];

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
     * @var array
     */
    protected $additionalSorting = [];

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
     * @param string $field
     * @return $this
     */
    public function setIdField(string $field = 'id')
    {
        $this->idField = $field;

        return $this;
    }

    /**
     * @return int|mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCount()
    {
        return $this->countQuery->getQuery()->getSingleScalarResult();
    }

    /**
     * @inheritdoc
     */
    public function getRecords()
    {
        $idField = $this->idField;
        $this->ids = array_map(function ($el) use ($idField) { return $el[$idField]; }, $this->getIds());

        if (count($this->ids) == 0) {
            return [];
        }

        $this->query
            ->andWhere($this->query->expr()->in($this->query->getRootAliases()[0] . '.' . $idField, ':ids'))
            ->orderBy('FIELD(' . $this->query->getRootAliases()[0] . '.' . $idField . ', :ids)')
            ->setParameter('ids', $this->ids)
        ;

        $records = $this->query->getQuery()->getArrayResult();

        if ($this->additionalInfoQuery) {
            $qb = $this->additionalInfoQuery;
            $alias = $qb->getRootAliases()[0];
            $qb
                ->andWhere($qb->expr()->in($alias . '.' . $idField, ':ids'))
                ->orderBy('FIELD(' . $alias . '.' . $idField . ', :ids)')
                ->setParameter('ids', $this->ids)
            ;
            $additionalInfo = $this->additionalInfoQuery->getQuery()->getArrayResult();

            $records = $this->arrayCombine($records, $additionalInfo);
        }

        return $records;
    }

    /**
     * @param QueryBuilder $countQuery
     * @return $this
     */
    public function setCountQuery(QueryBuilder $countQuery)
    {
        $this->countQuery = $countQuery;

        return $this;
    }

    /**
     * @return null
     */
    public function getCountQuery()
    {
        return $this->countQuery;
    }

    /**
     * @return null
     */
    public function getIds()
    {
        if ($this->maxResults && strtolower($this->maxResults) !== self::ALL_PARAMETER) {
            $this->idsQuery->setMaxResults($this->maxResults)->setFirstResult($this->getFirstResult());
        }

        $sort = $this->getSort();

        if (!empty($sort)) {
            $this->idsQuery->addOrderBy($sort['field'], $sort['direction']);
        } else {
            $this->idsQuery->addOrderBy($this->idsQuery->getRootAliases()[0] . '.' . $this->idField, 'asc');
        }

        foreach ($this->additionalSorting as $sorting) {
            $this->idsQuery->addOrderBy($sorting['field'], $sorting['direction']);
        }

        return $this->idsQuery->getQuery()->getArrayResult();
    }

    /**
     * @param QueryBuilder $idsQuery
     * @return $this
     */
    public function setIdsQuery(QueryBuilder $idsQuery)
    {
        $this->idsQuery = $idsQuery;

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

    /**
     * @param QueryBuilder $query
     * @return $this
     */
    public function setAdditionalQuery(QueryBuilder $query)
    {
        $this->additionalInfoQuery = $query;

        return $this;
    }

    /**
     * @return array
     */
    public function getSelectedIds()
    {
        return $this->ids;
    }

    /**
     * @param array $sort
     * @return $this
     */
    public function addAdditionalSorting(array $sort = [])
    {
        if (isset($sort['field']) && isset($sort['direction'])) {
            $this->additionalSorting[] = $sort;
        }

        return $this;
    }

    ####################################################
    #                  Method Helpers                  #
    ####################################################

    /**
     * @param array $targets
     * @param array $additions
     * @return array
     */
    protected function arrayCombine(array $targets, array $additions)
    {
        $tmp = [];
        foreach ($targets as $target){
            foreach ($additions as $addition){
                if($target[$this->idField] == $addition[$this->idField]){
                    $tmp[] = array_merge($target, $addition);
                    break;
                }
            }
        }
        return $tmp;
    }
}
