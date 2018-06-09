<?php

namespace NetBull\CoreBundle\Locale\Guessers;

/**
 * Class AbstractLocaleGuesser
 * @package NetBull\CoreBundle\Locale\Guessers
 */
abstract class AbstractLocaleGuesser implements LocaleGuesserInterface
{
    /**
     * @var string
     */
    protected $identifiedLocale;

    /**
     * Get the identified locale
     *
     * @return mixed
     */
    public function getIdentifiedLocale()
    {
        if (null === $this->identifiedLocale) {
            return false;
        }

        return $this->identifiedLocale;
    }
}
