<?php

namespace NetBull\CoreBundle\Locale\Matcher;

/**
 * Interface BestLocaleMatcher
 * @package NetBull\CoreBundle\Locale\Matcher
 */
interface BestLocaleMatcherInterface
{
    /**
     * @param $locale
     * @return mixed
     */
    public function match($locale);
}
