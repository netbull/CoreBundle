<?php

namespace NetBull\CoreBundle\Locale\Matcher;

use NetBull\CoreBundle\Locale\Information\AllowedLocalesProvider;

/**
 * Class DefaultBestLocaleMatcher
 * @package NetBull\CoreBundle\Locale\Matcher
 */
class DefaultBestLocaleMatcher implements BestLocaleMatcherInterface
{
    /**
     * @var AllowedLocalesProvider
     */
    private $allowedLocaleProvider;

    /**
     * DefaultBestLocaleMatcher constructor.
     * @param AllowedLocalesProvider $allowedLocales
     */
    public function __construct(AllowedLocalesProvider $allowedLocales)
    {
        $this->allowedLocaleProvider = $allowedLocales;
    }

    /**
     * {@inheritdoc}
     */
    public function match($locale)
    {
        $allowedLocales = $this->allowedLocaleProvider->getAllowedLocales();
        uasort($allowedLocales, function ($a, $b) {
            return strlen($b)-strlen($a);
        });
        foreach ($allowedLocales as $allowedLocale) {
            if (0 === strpos($locale, $allowedLocale)) {
                return $allowedLocale;
            }
        }
        return false;
    }
}
