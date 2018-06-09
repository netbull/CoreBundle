<?php

namespace NetBull\CoreBundle\Locale\Guessers;

use Symfony\Component\HttpFoundation\Request;

use NetBull\CoreBundle\Locale\Validator\MetaValidator;

/**
 * Class CookieLocaleGuesser
 * @package NetBull\CoreBundle\Locale\Guessers
 */
class CookieLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @var string
     */
    const LOCALE_COOKIE_NAME = '_l';

    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * Constructor
     *
     * @param MetaValidator $metaValidator    MetaValidator
     */
    public function __construct(MetaValidator $metaValidator)
    {
        $this->metaValidator = $metaValidator;
    }

    /**
     * Retrieve from cookie
     *
     * @param Request $request Request
     *
     * @return bool
     */
    public function guessLocale(Request $request)
    {
        if ($request->cookies->has(self::LOCALE_COOKIE_NAME) && $this->metaValidator->isAllowed($request->cookies->get(self::LOCALE_COOKIE_NAME))) {
            $this->identifiedLocale = $request->cookies->get(self::LOCALE_COOKIE_NAME);
            return true;
        }

        return false;
    }
}
