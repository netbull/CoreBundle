# NetBull CoreBundle

A Symfony utility bundle bundling the pieces NetBull apps share: entity-reference (AJAX/Select2)
and phone-number form types, a three-query Doctrine paginator with sortable-column Twig helpers,
custom DBAL types (spatial, integer range, phone number), a route exporter for frontend
JavaScript, and assorted Twig filters.

- **PHP:** >= 8.3
- **Symfony:** 7.4 (LTS)

## Installation

```console
composer require netbull/core-bundle
```

### Register the bundle

With Symfony Flex this is automatic. Otherwise add it to `config/bundles.php`:

```php
return [
    // ...
    NetBull\CoreBundle\NetBullCoreBundle::class => ['all' => true],
];
```

## Configuration

Everything is optional — the bundle works with an empty `netbull_core:` config. Defaults shown:

```yaml
# config/packages/netbull_core.yaml
netbull_core:
    # JS routing export (bin/console netbull:core:js-routing)
    js_routes_path: null        # output file, relative to the project dir, e.g. 'assets/js/router.js'
    js_type: 'js'               # 'js' (window.Netbull.Router) or 'es6' (ES module)

    # Defaults for the AjaxType / Select2Type widgets
    form_types:
        ajax:
            minimum_input_length: 1
            page_limit: 10
            allow_clear: false
            delay: 250
            language: 'en'
            cache: true

    # Classes/icons used by the pagination_sortable() Twig function (FontAwesome by default)
    paginator:
        sortable:
            icons:
                none: 'fa fa-sort'
                asc: 'fa fa-sort-up'
                desc: 'fa fa-sort-down'
            active_class: 'text-success'
            not_active_class: 'text-primary'
```

To use the bundle's form widgets, register its form themes:

```yaml
# config/packages/twig.yaml
twig:
    form_themes:
        - '@NetBullCore/Form/forms.html.twig'
        - '@NetBullCore/Form/phone_number.html.twig'   # or phone_number_bootstrap.html.twig
```

The markup is Bootstrap-flavored and uses FontAwesome icons; the select widgets emit
[Select2](https://select2.org/) data attributes — the app ships and initializes the JS itself.

## Pagination

The `Paginator` service runs three queries — a COUNT, an IDs query (LIMIT/OFFSET + ORDER BY on
primary keys only), and a data query restricted to those IDs — so large offsets stay fast and
joins never break the limit. Page (`page`/`currentPage`), page size (`perPage`/`pageSize`, the
literal `all` disables the limit) and sorting (`field` + `direction`) are sniffed from the
request automatically.

Have the repository implement `PaginatorRepositoryInterface`:

```php
class AccountRepository extends ServiceEntityRepository implements PaginatorRepositoryInterface
{
    public function getPaginationCount(array $params = []): QueryBuilder
    {
        return $this->createQueryBuilder('a')->select('COUNT(a.id)');
    }

    public function getPaginationIds(array $params = []): QueryBuilder
    {
        return $this->createQueryBuilder('a')->select('a.id');
    }

    public function getPaginationQuery(array $params = []): QueryBuilder
    {
        return $this->createQueryBuilder('a')->select('a', 'p')->leftJoin('a.profile', 'p');
    }
}
```

Wire the three builders in the controller (the service is autowirable):

```php
public function index(AccountRepository $repo, Paginator $paginator): JsonResponse
{
    $params = []; // your filters

    // default sort — guard it, setSorting() replaces the sorting sniffed from the request
    if (!$paginator->getSorting()) {
        $paginator->setSorting(new Sorting('a.createdAt', Sorting::DIRECTION_DESC));
    }

    $paginator
        ->setCountQuery($repo->getPaginationCount($params))
        ->setIdsQuery($repo->getPaginationIds($params))
        ->setQuery($repo->getPaginationQuery($params));

    return $this->json($paginator->paginateShort());
    // { items: [...], pagination: { currentPage, pageSize, totalItems } }
}
```

`paginate()` returns a richer pagination array (page ranges, route, sorting, ...) for classic
server-rendered lists; `setItemNormalizer(fn (array $row) => ...)` maps every item before output.
`PaginatorSimple` is a one-query variant for when you already hold the filtered ID list
(`setIds([['id' => 1], ...])` + `setQuery(...)`).

> **FIELD() requirement** — the data query re-orders rows with MySQL's `FIELD()`, which Doctrine
> doesn't know natively. Register it once (the bundle ships `beberlei/doctrineextensions` for
> this):
>
> ```yaml
> # config/packages/doctrine.yaml
> doctrine:
>     orm:
>         dql:
>             string_functions:
>                 FIELD: DoctrineExtensions\Query\Mysql\Field
> ```

### Sortable column headers in Twig

```twig
{# `pagination` is the array from paginator.paginate() #}
<th>{{ pagination_sortable(pagination, 'Name', 'a.name') }}</th>
```

Clicking cycles ascending → descending → cleared. A default `Sorting` must be set on the
paginator for the links to render. `query_inputs('q')` emits hidden inputs preserving the other
query parameters inside a GET filter form.

## Form types

| Type | What it does |
|------|--------------|
| `DynamicType` | Entity `<select>` that renders only the selected option(s); the app's JS loads the rest |
| `AjaxType` | `DynamicType` + remote search: generates an endpoint URL from a route, emits Select2 AJAX data attributes |
| `Select2Type` | `AjaxType` + full Select2 config (`allow_clear`, `delay`, `language`, `cache`, `tags`) |
| `EntityHiddenType` | Entity reference in a hidden input (id ↔ entity via model transformer) |
| `AutoCollectionType` | Core `CollectionType` rendered with a `data-prototype` + "Add new" button widget |
| `UnorderedCollectionType` | Collection keyed/matched by an identifier property instead of array position |
| `CompoundRangeType` | min/max integer pair stored as a `"min-max"` string |
| `MoneyType` | Core MoneyType with fixed `1.234,56`-style separators regardless of locale (`localize: true` opts back out) |
| `PhoneNumberType` | libphonenumber-backed input — single text or country-select + national number |
| `PointType` / `PointTextType` | `"lat, lng"` hidden/text input mapped to the `Point` value object |

```php
$builder->add('customer', AjaxType::class, [
    'class' => Customer::class,
    'text_property' => 'name',
    'remote_route' => 'app_customers_search',   // generated with ?perPage=<page_limit>
    'minimum_input_length' => 2,
    'placeholder' => 'Search customers...',
    'multiple' => false,
]);
```

The endpoint receives the Select2 request and should return the stock Select2 shape
`{ results: [{ id, text }], pagination: { more } }` (or the app's JS overrides
`processResults`). Submitted values are entity primary keys (`primary_key` option, default
`id`).

`UnorderedCollectionType` matches submitted rows to existing items by an identifier instead of
their index, so reordered/partial submissions behave predictably; duplicate identifiers are
rejected with a form error:

```php
$builder->add('contacts', UnorderedCollectionType::class, [
    'entry_type' => ContactType::class,
    'property' => 'id',
    'allow_add' => true,
    'allow_delete' => true,
]);
```

```php
$builder->add('phone', PhoneNumberType::class, [
    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
    'country_choices' => ['BG', 'DE', 'GB'],
    'preferred_country_choices' => ['BG'],
]);
```

## Doctrine utilities

### DBAL column types

Register the ones you use:

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        types:
            point: NetBull\CoreBundle\ORM\Types\Point
            range: NetBull\CoreBundle\ORM\Types\Range
            phone_number: NetBull\CoreBundle\ORM\Types\PhoneNumber
            geometry: NetBull\CoreBundle\ORM\Types\Geometry
            linestring: NetBull\CoreBundle\ORM\Types\Linestring
            multilinestring: NetBull\CoreBundle\ORM\Types\MultiLinestring
            multipolygon: NetBull\CoreBundle\ORM\Types\Multipolygon
```

```php
use NetBull\CoreBundle\ORM\Objects\Point;

#[ORM\Column(type: 'point', nullable: true)]
private ?Point $coordinates = null;          // new Point($latitude, $longitude)

#[ORM\Column(type: 'phone_number', nullable: true)]
private ?\libphonenumber\PhoneNumber $phone = null;   // stored as E.164, VARCHAR(35)
```

- `point` maps to a MySQL `POINT` column via `ST_PointFromText`/`ST_AsText`.
- `range` stores `NetBull\CoreBundle\ORM\Objects\Range` as a `"min-max"` string — create the
  column manually (string), schema generation does not emit a type for it.
- The four spatial types (`geometry`, `linestring`, `multilinestring`, `multipolygon`) validate
  WKT through [geoPHP](https://github.com/itamair/geoPHP) — `composer require itamair/geophp`
  to use them. All spatial SQL targets MySQL/MariaDB. Note that `geometry` accepts
  POLYGON/MULTIPOLYGON WKT only, despite the generic name.

### GREATEST() in DQL

```yaml
doctrine:
    orm:
        dql:
            numeric_functions:
                GREATEST: NetBull\CoreBundle\Query\Mysql\Greatest
```

```sql
SELECT GREATEST(p.updatedAt, p.createdAt) FROM App\Entity\Page p
```

## Phone number validation

```php
use NetBull\CoreBundle\Validator\Constraints\PhoneNumber;

#[PhoneNumber(defaultRegion: 'BG', type: PhoneNumber::MOBILE)]
private ?string $phone = null;
```

Accepts strings, stringables or `libphonenumber\PhoneNumber` objects; with the default
`defaultRegion` (`ZZ`) the value must be in international format (`+359...`). Type-specific
messages out of the box (`mobile`, `fixed_line`, `toll_free`, ...). Pair with the
`phone_number` DBAL type and `PhoneNumberType` form type.

In Twig:

```twig
{{ user.phone|phone_number_format }}   {# INTERNATIONAL by default #}
{% if user.phone is phone_number_of_type(constant('libphonenumber\\PhoneNumberType::MOBILE')) %}...{% endif %}
```

## JS routing

Export routes to the frontend without FOSJsRoutingBundle. Only routes marked with the
`expose` option are dumped:

```php
#[Route('/api/items/{id}', name: 'app_item', options: ['expose' => true])]
```

```yaml
netbull_core:
    js_routes_path: 'assets/js/router.js'
    js_type: 'js'    # or 'es6'
```

```console
bin/console netbull:core:js-routing            # writes <project>/assets/js/router.js
bin/console netbull:core:js-routing --target=/tmp/router.js
```

The `js` flavor defines `window.Netbull.Router` with one function per route:

```js
const url = window.Netbull.Router.app_item(42); // "/api/items/42"
```

The `es6` flavor default-exports a router module:

```js
import Router from './router';

const url = Router.get('app_item', 42); // "/api/items/42"
```

Parameters are substituted positionally as-is (no URL-encoding).

## Twig helpers

| Helper | Kind | Example |
|--------|------|---------|
| `pagination_sortable(pagination, label, field)` | function | sortable `<th>` link (see Pagination) |
| `query_inputs(currentField)` | function | hidden inputs preserving current query params |
| `helperText(text)` | function | FontAwesome question-mark tooltip icon |
| `lipsum(length = 30)` | function | lorem-ipsum filler text |
| `inflect(count = 0)` | filter | `{{ 'item'\|inflect(items\|length) }}` → "item"/"items" |
| `titleize` | filter | `{{ 'first_name'\|titleize }}` → "First Name" |
| `country(locale = '')` | filter | `{{ 'BG'\|country }}` → "Bulgaria" |
| `strip_tags_super` | filter | extracts the `<body>` text of a full HTML document |
| `phone_number_format(format?)` | filter | formats a `libphonenumber\PhoneNumber` |
| `phone_number_of_type(type)` | test | checks the number type |

## Utilities

- `Inflect` — English pluralize/singularize/titleize/underscore/humanize (powers the Twig filters).
- `PrintLabels` — TCPDF subclass that prints text labels onto A4 sticker sheets (built-in
  `Labels` 5×13 and `OrderLabels` 3×7 grids). Legacy; requires `composer require tecnickcom/tcpdf`.

## Development

```console
composer test        # PHPUnit
composer phpstan     # static analysis
composer cs-check    # coding standards (php-cs-fixer, dry-run)
composer check       # all of the above
```

## License

[MIT](LICENSE)
