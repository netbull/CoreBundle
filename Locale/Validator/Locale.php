<?php

namespace NetBull\CoreBundle\Locale\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class Locale
 * @package NetBull\CoreBundle\Locale\Validator
 */
class Locale extends Constraint
{
    /**
     * @var string
     */
    public $message = 'The locale "%string%" is not a valid locale';

    /**
     * {@inheritDoc}
     */
    public function validatedBy()
    {
        return 'netbull_core.validator.locale';
    }
}
