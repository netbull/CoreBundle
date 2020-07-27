<?php

namespace NetBull\CoreBundle\Twig;

use Symfony\Component\Intl\Intl;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use NetBull\CoreBundle\Utils\Inflect;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Class CoreExtension
 * @package NetBull\CoreBundle\Twig
 */
class CoreExtension extends AbstractExtension
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * CoreExtension constructor.
     * @param RouterInterface $router
     * @param RequestStack $requestStack
     */
    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return [
            new TwigFunction('pagination_sortable', [$this, 'sortable'], ['is_safe' => ['html']]),
            new TwigFunction('queryInputs', [$this, 'buildQueryInputs'], ['is_safe' => ['html']]),
            new TwigFunction('helperText', [$this, 'buildHelperText'], ['is_safe' => ['html']]),
            new TwigFunction ('lipsum', [$this, 'loremIpsum'])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('rename_pipe', [$this, 'renameByPipe']),
            new TwigFilter('inflect', [$this, 'inflect']),
            new TwigFilter('titleize', [$this, 'titleize']),
            new TwigFilter('country', [$this, 'getCountryName']),
            new TwigFilter('format_page_title', [$this, 'formatPageTitle']),
            new TwigFilter('strip_tags_super', [$this, 'stripTagsSuper']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return [
            new TwigTest('numeric', [$this, 'numericTest']),
        ];
    }

    #########################################
    #              Functions                #
    #########################################
    /**
     * @param $pagination
     * @param $label
     * @param $field
     * @return string
     */
    public function sortable($pagination, $label, $field)
    {
        if (in_array($field, $pagination['sort'])) {
            $direction = 'asc';
            if (isset($pagination['sort']['direction']) && ($pagination['sort']['direction'] == 'asc' || $pagination['sort']['direction'] == 'desc')) {
                $direction = $pagination['sort']['direction'];
            }

            $newDirection = ($direction == 'asc') ? 'desc' : 'asc';
            $hint = ($direction === 'asc') ? 'Descending' : 'Ascending';

            // If we are on DESC sorting next should be the initial state to clear the sorting
            if ($direction == 'desc') {
                unset($pagination['sort']['field']);
                unset($pagination['sort']['direction']);
                unset($pagination['routeParams']['field']);
                unset($pagination['routeParams']['direction']);
                $hint = 'clear';
                $params = [];
            } else {
                $params = array_merge($pagination['sort'], [
                    'field'     => $field,
                    'direction' => $newDirection
                ]);
            }
            $link = $this->router->generate($pagination['route'], array_merge($pagination['routeParams'], $params));

            $icon = 'desc' === $direction ? 'down' : 'up';
            $string = sprintf('<a class="text-success" href="%s" title="Sort %s">%s <i class="fa fa-sort-%s"></i></a>', $link, $hint, $label, $icon);
        } else {
            $link = $this->router->generate($pagination['route'], array_merge($pagination['routeParams'], $pagination['sort'], [
                'field'     => $field,
                'direction' => 'asc'
            ]));
            $string = sprintf('<a class="text-primary" href="%s" title="Sort Ascending">%s <i class="fa fa-sort"></i></a>', $link, $label);
        }

        return $string;
    }

    /**
     * Build Hidden fields based on the URL parameters
     * @param $currentField
     * @return string
     */
    public function buildQueryInputs($currentField)
    {
        $request = $this->requestStack->getCurrentRequest();
        $fields = '';
        foreach ($request->query->all() as $field => $value) {
            // Exclude the current field and the PAGE parameter
            if($field !== $currentField && $field !== 'page'){
                $fields .= sprintf('<input type="hidden" name="%s" value="%s">', $field, $value);
            }
        }

        return $fields;
    }

    /**
     * Build Helper icon
     * @param $text
     * @return mixed
     */
    public function buildHelperText($text)
    {
        return sprintf('<i class="fa fa-question-circle text-primary helper-text" title="%s"></i>', $text);
    }

    /**
     * @param int $length
     * @return string
     */
    public function loremIpsum($length = 30) {
        $string = [];
        $words = [
            'lorem',        'ipsum',       'dolor',        'sit',
            'amet',         'consectetur', 'adipiscing',   'elit',
            'a',            'ac',          'accumsan',     'ad',
            'aenean',       'aliquam',     'aliquet',      'ante',
            'aptent',       'arcu',        'at',           'auctor',
            'augue',        'bibendum',    'blandit',      'class',
            'commodo',      'condimentum', 'congue',       'consequat',
            'conubia',      'convallis',   'cras',         'cubilia',
            'cum',          'curabitur',   'curae',        'cursus',
            'dapibus',      'diam',        'dictum',       'dictumst',
            'dignissim',    'dis',         'donec',        'dui',
            'duis',         'egestas',     'eget',         'eleifend',
            'elementum',    'enim',        'erat',         'eros',
            'est',          'et',          'etiam',        'eu',
            'euismod',      'facilisi',    'facilisis',    'fames',
            'faucibus',     'felis',       'fermentum',    'feugiat',
            'fringilla',    'fusce',       'gravida',      'habitant',
            'habitasse',    'hac',         'hendrerit',    'himenaeos',
            'iaculis',      'id',          'imperdiet',    'in',
            'inceptos',     'integer',     'interdum',     'justo',
            'lacinia',      'lacus',       'laoreet',      'lectus',
            'leo',          'libero',      'ligula',       'litora',
            'lobortis',     'luctus',      'maecenas',     'magna',
            'magnis',       'malesuada',   'massa',        'mattis',
            'mauris',       'metus',       'mi',           'molestie'
        ];

        for ($i=0; $i < $length; $i++) {
            $string[] = $words[rand(0, 99)];
        }

        return implode(' ', $string);
    }

    #########################################
    #                Filters                #
    #########################################

    /**
     * Pluralize or Singularize a string
     * @param string $string
     * @param int $pluralize
     * @return string
     */
    public function inflect(string $string, $pluralize = 0) : string
    {
        return ($pluralize) ? Inflect::pluralize($string) : Inflect::singularize($string);
    }

    /**
     * @param string $string
     * @return mixed|null|string|string[]
     */
    public function titleize(string $string)
    {
        return Inflect::titleize($string);
    }

    /**
     * @param string $code
     * @param string $locale
     * @return string
     */
    public function getCountryName(string $code, string $locale = '') : string
    {
        $countries = Intl::getRegionBundle()->getCountryNames($locale);

        return array_key_exists($code, $countries)
            ? $countries[$code]
            : $code;
    }

    /**
     * @param string $title
     * @return string
     */
    public function formatPageTitle(string $title) : string
    {
        return sprintf('%s - %s', $title, 'NetBull');
    }

    /**
     * @param string $string
     * @return string
     */
    public function stripTagsSuper(string $string) : string
    {
        if (false === strpos($string, '<body')) {
            $text = $string;
        } else {
            $crawler = new Crawler($string);
            $body = $crawler->filter('body');
            $text = $body->text();
        }

        return $text;
    }

    #########################################
    #                 Tests                 #
    #########################################

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'core.extension';
    }
}
