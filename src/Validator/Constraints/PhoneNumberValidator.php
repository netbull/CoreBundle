<?php

namespace NetBull\CoreBundle\Validator\Constraints;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;
use Symfony\Component\Validator\Constraint;
use libphonenumber\PhoneNumber as PhoneNumberObject;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PhoneNumberValidator extends ConstraintValidator
{
    /**
     * @param $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        if (false === $value instanceof PhoneNumberObject) {
            $value = (string) $value;

            if (!empty($constraint->defaultRegions)) {
                $match = false;
                foreach ($constraint->defaultRegions as $defaultRegion) {
                    try {
                        $phoneNumber = $phoneUtil->parse($value, $defaultRegion);
                        $match = true;
                        break;
                    } catch (NumberParseException) {}
                }

                if (!$match) {
                    $this->addViolation($value, $constraint);
                    return;
                }
            } else {
                try {
                    $phoneNumber = $phoneUtil->parse($value, $constraint->defaultRegion);
                } catch (NumberParseException) {
                    $this->addViolation($value, $constraint);
                    return;
                }
            }
        } else {
            $phoneNumber = $value;
            $value = $phoneUtil->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);
        }

        if (false === $phoneUtil->isValidNumber($phoneNumber)) {
            $this->addViolation($value, $constraint);
            return;
        }

        $validTypes = match ($constraint->getType()) {
            PhoneNumber::FIXED_LINE => [PhoneNumberType::FIXED_LINE, PhoneNumberType::FIXED_LINE_OR_MOBILE],
            PhoneNumber::MOBILE => [PhoneNumberType::MOBILE, PhoneNumberType::FIXED_LINE_OR_MOBILE],
            PhoneNumber::PAGER => [PhoneNumberType::PAGER],
            PhoneNumber::PERSONAL_NUMBER => [PhoneNumberType::PERSONAL_NUMBER],
            PhoneNumber::PREMIUM_RATE => [PhoneNumberType::PREMIUM_RATE],
            PhoneNumber::SHARED_COST => [PhoneNumberType::SHARED_COST],
            PhoneNumber::TOLL_FREE => [PhoneNumberType::TOLL_FREE],
            PhoneNumber::UAN => [PhoneNumberType::UAN],
            PhoneNumber::VOIP => [PhoneNumberType::VOIP],
            PhoneNumber::VOICEMAIL => [PhoneNumberType::VOICEMAIL],
            default => [],
        };

        if (count($validTypes)) {
            $type = $phoneUtil->getNumberType($phoneNumber);

            if (false === in_array($type, $validTypes)) {
                $this->addViolation($value, $constraint);
            }
        }
    }

    /**
     * @param $value
     * @param Constraint $constraint
     */
    private function addViolation($value, Constraint $constraint): void
    {
        $this->context->addViolation(
            $constraint->getMessage(),
            array('{{ type }}' => $constraint->getType(), '{{ value }}' => $value)
        );
    }
}
