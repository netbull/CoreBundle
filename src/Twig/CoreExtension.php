<?php

namespace NetBull\CoreBundle\Twig;

use NetBull\CoreBundle\Paginator\Sorting;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use NetBull\CoreBundle\Utils\Inflect;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class CoreExtension extends AbstractExtension
{
    /**
     * @param RouterInterface $router
     * @param RequestStack $requestStack
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(private RouterInterface $router, private RequestStack $requestStack, private ParameterBagInterface $parameterBag)
    {
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('pagination_sortable', [$this, 'sortable'], ['is_safe' => ['html']]),
            new TwigFunction('query_inputs', [$this, 'buildQueryInputs'], ['is_safe' => ['html']]),
            new TwigFunction('helperText', [$this, 'buildHelperText'], ['is_safe' => ['html']]),
            new TwigFunction ('lipsum', [$this, 'loremIpsum'])
        ];
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('rename_pipe', [$this, 'renameByPipe']),
            new TwigFilter('inflect', [$this, 'inflect']),
            new TwigFilter('titleize', [$this, 'titleize']),
            new TwigFilter('country', [$this, 'getCountryName']),
            new TwigFilter('strip_tags_super', [$this, 'stripTagsSuper']),
        ];
    }

    /**
     * @return TwigTest[]
     */
    public function getTests(): array
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
    public function sortable($pagination, $label, $field): string
    {
        $activeClass = $this->parameterBag->get('netbull_core.paginator.sortable.active_class');
        $notActiveClass = $this->parameterBag->get('netbull_core.paginator.sortable.not_active_class');

        /** @var Sorting|null $sort */
        $sort = $pagination['sorting'][0] ?? null;
        if (!$sort) {
            return $label;
        }

        if ($field === $sort->getField()) {
            $direction = $sort->getDirection();

            $newDirection = $direction === Sorting::DIRECTION_ASC ? Sorting::DIRECTION_DESC : Sorting::DIRECTION_ASC;
            $hint = Sorting::DIRECTION_ASC === $direction ? 'Descending' : 'Ascending';

            // If we are on DESC sorting next should be the initial state to clear the sorting
            if (Sorting::DIRECTION_DESC === $direction) {
                unset($pagination['routeParams']['field']);
                unset($pagination['routeParams']['direction']);
                $hint = 'clear';
                $params = [];
            } else {
                $params = [
                    'field' => $field,
                    'direction' => $newDirection
                ];
            }
            $link = $this->router->generate($pagination['route'], array_merge($pagination['routeParams'], $params));
            $icon = $this->parameterBag->get('netbull_core.paginator.sortable.icons.'.$direction);
            $string = sprintf('<a class="%s" href="%s" title="Sort %s">%s <i class="%s"></i></a>', $activeClass, $link, $hint, $label, $icon);
        } else {
            $link = $this->router->generate($pagination['route'], array_merge($pagination['routeParams'], [
                'field' => $field,
                'direction' => Sorting::DIRECTION_ASC
            ]));
            $icon = $this->parameterBag->get('netbull_core.paginator.sortable.icons.none');
            $string = sprintf('<a class="%s" href="%s" title="Sort Ascending">%s <i class="%s"></i></a>', $notActiveClass, $link, $label, $icon);
        }

        return $string;
    }

    /**
     * Build Hidden fields based on the URL parameters
     * @param $currentField
     * @return string
     */
    public function buildQueryInputs($currentField): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $fields = '';
        foreach ($request->query->all() as $field => $value) {
            // Exclude the current field and the PAGE parameter
            if ($field !== $currentField && $field !== 'page') {
                if (is_array($value)) {
                    foreach($value as $val) {
                        $fields .= sprintf('<input type="hidden" name="%s" value="%s">', $field.'[]', $val);
                    }
                } else {
                    $fields .= sprintf('<input type="hidden" name="%s" value="%s">', $field, $value);
                }
            }
        }

        return $fields;
    }

    /**
     * Build Helper icon
     * @param $text
     * @return mixed
     */
    public function buildHelperText($text): mixed
    {
        return sprintf('<i class="fa fa-question-circle text-primary helper-text" title="%s"></i>', $text);
    }

    /**
     * @param int $length
     * @return string
     */
    public function loremIpsum(int $length = 30): string
    {
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
    public function inflect(string $string, int $pluralize = 0) : string
    {
        return $pluralize === 0 || $pluralize > 1 ? Inflect::pluralize($string) : Inflect::singularize($string);
    }

    /**
     * @param string $string
     * @return mixed|null|string|string[]
     */
    public function titleize(string $string): mixed
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
        if (empty($code)) {
            return '';
        }
        return Countries::getName($code, $locale);
    }

    /**
     * @param string $string
     * @return string
     */
    public function stripTagsSuper(string $string) : string
    {
        if (!str_contains($string, '<body')) {
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
     * @return string
     */
    public function getName(): string
    {
        return 'netbull_core.extension';
    }
}
