<?php

namespace NetBull\CoreBundle\Locale\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use NetBull\CoreBundle\Locale\Information\AllowedLocalesProvider;

/**
 * Class LocaleAllowedValidator
 * @package NetBull\CoreBundle\Locale\Validator
 */
class LocaleAllowedValidator extends ConstraintValidator
{
    /**
     * @var AllowedLocalesProvider
     */
    private $allowedLocalesProvider;

    /**
     * @var bool
     */
    private $intlExtension;

    /**
     * Constructor
     *
     * @param AllowedLocalesProvider  $allowedLocalesProvider  allowed locales provided by service
     * @param bool                    $intlExtension           Whether the intl extension is installed
     */
    public function __construct(AllowedLocalesProvider $allowedLocalesProvider = null, $intlExtension = false)
    {
        $this->allowedLocalesProvider   = $allowedLocalesProvider;
        $this->intlExtension            = $intlExtension;
    }

    /**
     * Validates a Locale
     *
     * @param string     $locale     The locale to be validated
     * @param Constraint $constraint Locale Constraint
     *
     * @throws UnexpectedTypeException
     */
    public function validate($locale, Constraint $constraint)
    {
        if (null === $locale || '' === $locale) {
            return;
        }

        if (!is_scalar($locale) && !(is_object($locale) && method_exists($locale, '__toString'))) {
            throw new UnexpectedTypeException($locale, 'string');
        }

        $locale = (string) $locale;

        if (!in_array($locale, $this->getAllowedLocales())) {
            $this->context->addViolation($constraint->message, ['%string%' => $locale]);
        }
    }

    /**
     * @return array
     */
    protected function getAllowedLocales()
    {
        if (null !== $this->allowedLocalesProvider) {
            return $this->allowedLocalesProvider->getAllowedLocales();
        } else {
            return [];
        }
    }
}
