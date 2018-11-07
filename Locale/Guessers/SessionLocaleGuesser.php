<?php

namespace NetBull\CoreBundle\Locale\Guessers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

use NetBull\CoreBundle\Locale\Validator\MetaValidator;

/**
 * Class SessionLocaleGuesser
 * @package NetBull\CoreBundle\Locale\Guessers
 */
class SessionLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * @var string
     */
    private $sessionVariable;

    /**
     * SessionLocaleGuesser constructor.
     * @param Session $session
     * @param MetaValidator $metaValidator
     * @param string $sessionVariable
     */
    public function __construct(Session $session, MetaValidator $metaValidator, string $sessionVariable)
    {
        $this->session = $session;
        $this->metaValidator = $metaValidator;
        $this->sessionVariable = $sessionVariable;
    }

    /**
     * Guess the locale based on the session variable
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function guessLocale(Request $request)
    {
        if ($this->session->has($this->sessionVariable)) {
            $locale = $this->session->get($this->sessionVariable);
            if (!$this->metaValidator->isAllowed($locale)) {
                return false;
            }
            $this->identifiedLocale = $this->session->get($this->sessionVariable);
            return true;
        }
        return false;
    }

    /**
     * Sets the locale in the session
     *
     * @param string $locale Locale
     * @param bool   $force  Force write session
     */
    public function setSessionLocale($locale, $force = false)
    {
        if (!$this->session->has($this->sessionVariable) || $force) {
            $this->session->set($this->sessionVariable, $locale);
        }
    }
}
