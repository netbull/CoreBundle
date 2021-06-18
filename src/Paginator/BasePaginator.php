<?php

namespace NetBull\CoreBundle\Paginator;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class BasePaginator
 * @package NetBull\CoreBundle\Paginator
 */
abstract class BasePaginator
{
    const ALL_PARAMETER = 'all';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var int
     */
    protected $maxResults = null;

    /**
     * @var int|null
     */
    protected $page = 1;

    /**
     * @var mixed
     */
    protected $route;

    /**
     * @var array
     */
    protected $routeParams;

    /**
     * @var array
     */
    protected $sorting = [];

    /**
     * @var string|null
     */
    protected $queryFilter = '';

    /**
     * Paginator constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();

        $this->route = ($this->request) ? $this->request->attributes->get('_route') : null;
        $this->routeParams = ($this->request) ? array_merge($this->request->query->all(), $this->request->attributes->get('_route_params', [])) : null;

        $params = ($this->request) ? array_merge($this->request->query->all(),$this->request->request->all()) : [];
        if (isset($params['perPage']) && $params['perPage'] && strtolower($params['perPage']) !== self::ALL_PARAMETER) {
            $this->maxResults = (int)$params['perPage'] ?? 20;
        }
        $this->page = (isset($params['page']) && (int)$params['page']) ? (int)$params['page'] : 1;
        $this->queryFilter = (isset($params['query'])) ? $params['query'] : '';

        // Sniff the sorting options
        if (isset($params['field']) && !empty($params['field'])) {
            $direction = 'asc';
            if( isset($params['direction']) && ($params['direction'] == 'asc' || $params['direction'] == 'desc') ){
                $direction = $params['direction'];
            }

            $this->sorting[] = new Sorting($params['field'], $direction);
        }

        return $this;
    }

    /**
     * Handle the pagination
     * @return array
     */
    public function paginate(): array
    {
        $totalCount = $this->getCount();

        $pageCount = $this->maxResults ? intval(ceil($totalCount / $this->maxResults)) : 1;
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
            'current' => (int)$current,
            'numItemsPerPage' => $this->maxResults ?? ucfirst(self::ALL_PARAMETER),
            'first' => 1,
            'pageCount' => $pageCount,
            'totalCount' => $totalCount,
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

        $items = $this->getRecords();
        if ($items !== null) {
            $pagination['currentItemCount'] = (int)$totalCount;
            $pagination['firstItemNumber'] = $this->maxResults ? (($current - 1) * $this->maxResults) + 1 : 1;
            $pagination['lastItemNumber'] =  $pagination['firstItemNumber'] + $pagination['currentItemCount'] - 1;
        }

        return [
            'items' => $items,
            'pagination' => $pagination
        ];
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
        $this->sorting = [];
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
