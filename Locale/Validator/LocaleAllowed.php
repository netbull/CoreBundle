<?php

namespace NetBull\CoreBundle\Locale\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class LocaleAllowed
 * @package NetBull\CoreBundle\Locale\Validator
 */
class LocaleAllowed extends Constraint
{
    /**
     * @var string
     */
    public $message = 'The locale "%string%" is not allowed by application configuration.';

    /**
     * {@inheritDoc}
     */
    public function validatedBy()
    {
        return 'netbull_core.validator.locale_allowed';
    }
}
