<?php

namespace NetBull\CoreBundle\Locale\Guessers;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface LocaleGuesserInterface
 * @package NetBull\CoreBundle\Locale\Guessers
 */
interface LocaleGuesserInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    public function guessLocale(Request $request);

    /**
     * @return mixed
     */
    public function getIdentifiedLocale();
}
