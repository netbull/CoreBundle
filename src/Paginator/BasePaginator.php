<?php

namespace NetBull\CoreBundle\Paginator;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class BasePaginator
{
    const ALL_PARAMETER = 'all';

    /**
     * @var Request|null
     */
    protected ?Request $request;

    /**
     * @var int|null
     */
    protected ?int $maxResults = null;

    /**
     * @var int|null
     */
    protected ?int $page = 1;

    /**
     * @var mixed
     */
    protected $route;

    /**
     * @var array|null
     */
    protected ?array $routeParams;

    /**
     * @var array
     */
    protected array $sorting = [];

    /**
     * @var string|null
     */
    protected $queryFilter = '';

    /**
     * @param RequestStack $requestStack
     *
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();

        $this->route = ($this->request) ? $this->request->attributes->get('_route') : null;
        $this->routeParams = ($this->request) ? array_merge($this->request->query->all(), $this->request->attributes->get('_route_params') ?? []) : null;

        $params = ($this->request) ? array_merge($this->request->query->all(),$this->request->request->all()) : [];
        $this->maxResults = 20;
        foreach (['perPage', 'pageSize'] as $maxResultsParam) {
            if (array_key_exists($maxResultsParam, $params)) {
                $this->maxResults = (int)$params[$maxResultsParam] ?? 20;
                break;
            }
            if (strtolower($params[$maxResultsParam]) === self::ALL_PARAMETER) {
                $this->maxResults = null;
                break;
            }
        }

        foreach (['page', 'currentPage'] as $pageParam) {
            if (array_key_exists($pageParam, $params)) {
                $this->page = (int)$params[$pageParam] ?? 1;
                break;
            }
        }
        $this->queryFilter = (isset($params['query'])) ? $params['query'] : '';

        // Sniff the sorting options
        if (!empty($params['field'])) {
            try {
                $this->sorting[] = new Sorting($params['field'], $params['direction']);
            } catch (InvalidArgumentException $e) {}
        }

        return $this;
    }

    /**
     * @param bool $reset
     * @return array
     */
    private function doPaginate(bool $reset = false): array
    {
        $itemsCount = $this->getCount();
        $records = $this->getRecords();

        if ($reset) {
            $this->reset();
        }

        return [ $itemsCount, $records ];
    }

    /**
     * @param bool $reset
     * @return array
     */
    public function paginate(bool $reset = false): array
    {
        list($itemsCount, $items) = $this->doPaginate($reset);

        $pageCount = $this->maxResults ? intval(ceil($itemsCount / $this->maxResults)) : 1;
        $current = $this->page;
        $pageRange = 5;

        if ($pageCount < $current) {
            $current = $pageCount;
        }

        if ($pageRange > $pageCount) {
            $pageRange = $pageCount;
        }

        $delta = ceil($pageRange / 2);

        if ($current - $delta > $pageCount - $pageRange) {
            $pages = range($pageCount - $pageRange + 1, $pageCount);
        } else {
            if ($current - $delta < 0) {
                $delta = $current;
            }

            $offset = $current - $delta;
            $pages = range($offset + 1, $offset + $pageRange);
        }

        $proximity = floor($pageRange / 2);

        $startPage = $current - $proximity;
        $endPage = $current + $proximity;

        if ($startPage < 1) {
            $endPage = min($endPage + (1 - $startPage), $pageCount);
            $startPage = 1;
        }

        if ($endPage > $pageCount) {
            $startPage = max($startPage - ($endPage - $pageCount), 1);
            $endPage = $pageCount;
        }

        $pagination = [
            'last' => $pageCount,
            'current' => (int)$current, // @deprecated: use currentPage as this will be removed in future
            'currentPage' => (int)$current,
            'numItemsPerPage' => $this->maxResults ?? ucfirst(self::ALL_PARAMETER), // @deprecated: use pageSize as this will be removed in future
            'pageSize' => $this->maxResults ?? ucfirst(self::ALL_PARAMETER),
            'first' => 1,
            'pageCount' => $pageCount,
            'totalCount' => $itemsCount, // @deprecated: use totalItems as this will be removed in future
            'totalItems' => $itemsCount,
            'pageRange' => $pageRange,
            'startPage' => (int)$startPage,
            'endPage' => (int)$endPage,
            'route' => $this->route,
            'routeParams' => $this->routeParams,
            'query' => $this->queryFilter,
            'pageParameterName' => 'page',
            'sorting' => $this->sorting, // ToDo: check the template paginator how will handle this..
            'sort' => [] // deprecated: will be removed after the macros sync
        ];

        if ($current - 1 > 0) {
            $pagination['previous'] = $current - 1;
        }

        if ($current + 1 <= $pageCount) {
            $pagination['next'] = $current + 1;
        }

        $pagination['pagesInRange'] = $pages;
        $pagination['firstPageInRange'] = min($pages);
        $pagination['lastPageInRange'] = max($pages);

        $pagination['currentItemCount'] = $itemsCount;
        $pagination['firstItemNumber'] = $this->maxResults ? (($current - 1) * $this->maxResults) + 1 : 1;
        $pagination['lastItemNumber'] =  $pagination['firstItemNumber'] + $pagination['currentItemCount'] - 1;

        return [
            'items' => $items,
            'pagination' => $pagination
        ];
    }

    /**
     * @param bool $reset
     * @return array
     */
    public function paginateShort(bool $reset = false): array
    {
        list($itemsCount, $items) = $this->doPaginate($reset);

        $pagination = [
            'currentPage' => (int)$this->page,
            'pageSize' => $this->maxResults ?? ucfirst(self::ALL_PARAMETER),
            'totalItems' => $itemsCount,
        ];

        return [
            'items' => $items,
            'pagination' => $pagination
        ];
    }

    public function reset()
    {
    }

    /**
     * @return int
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return $this
     */
    public function setPage(int $page): BasePaginator
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }

    /**
     * @param $maxResults
     * @return $this
     */
    public function setMaxResults($maxResults): BasePaginator
    {
        if (strtolower($maxResults) !== self::ALL_PARAMETER) {
            $this->maxResults = $maxResults;
        } else {
            $this->maxResults = null;
        }

        return $this;
    }

    /**
     * @return Sorting[]
     */
    public function getSorting(): array
    {
        return $this->sorting;
    }

    /**
     * @param Sorting[]|Sorting|array $sorting
     *  - array of Sorting instances
     *  - single Sorting instance
     *  - array in format ['field', 'direction']
     * @return $this
     */
    public function setSorting($sorting): BasePaginator
    {
        if (is_array($sorting)) {
            foreach ($sorting as $sort) {
                try {
                    $this->sorting[] = $this->normalizeSort($sort);
                } catch (InvalidArgumentException $e) {}
            }
        } else {
            try {
                $this->sorting[] = $this->normalizeSort($sorting);
            } catch (InvalidArgumentException $e) {}
        }

        return $this;
    }

    /**
     * @param $sort
     * @return Sorting
     * @throws InvalidArgumentException
     */
    private function normalizeSort($sort): Sorting
    {
        if ($sort instanceof Sorting) {
            return $sort;
        }

        if (is_array($sort)) {
            list($field, $direction) = array_values($sort);
            return new Sorting($field, $direction);
        }

        throw new InvalidArgumentException("Value \"$sort\" is not a valid Sorting");
    }

    /**
     * @param Sorting $sorting
     * @return $this
     */
    public function addSorting(Sorting $sorting): BasePaginator
    {
        $this->sorting[] = $sorting;
        return $this;
    }

    /**
     * @return int
     */
    public function getFirstResult()
    {
        if (!$this->maxResults) {
            return 0;
        }

        return ($this->page == 1) ? 0 : ($this->page - 1) * $this->maxResults;
    }

    /**
     * @return int
     */
    abstract public function getCount(): int;

    /**
     * @return array
     */
    abstract public function getRecords(): array;
}
