<?php

namespace NetBull\CoreBundle\Locale;

/**
 * Class LocaleMap
 * @package NetBull\CoreBundle\Locale
 */
class LocaleMap
{
    /**
     * @var array
     */
    private $map = [];

    /**
     * @param array $map topLevelDomain locale map, [tld => locale]
     */
    function __construct(array $map = [])
    {
        $this->map = $map;
    }

    /**
     * @param $tdl
     * @return bool|mixed
     */
    public function getLocale($tdl)
    {
        if (isset($this->map[$tdl]) && $this->map[$tdl]) {
            return $this->map[$tdl];
        }

        return false;
    }
}
