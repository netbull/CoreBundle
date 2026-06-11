<?php

namespace NetBull\CoreBundle\Twig;

use InvalidArgumentException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigTest;

class PhoneNumberExtension extends AbstractExtension
{
    protected PhoneNumberUtil $phoneNumberUtil;

    public function __construct()
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('phone_number_format', [$this, 'format']),
        ];
    }

    /**
     * Format a phone number.
     *
     * @param PhoneNumber $phoneNumber phone number
     * @param PhoneNumberFormat $format format, or format constant name
     *
     * @return string formatted phone number
     */
    public function format(PhoneNumber $phoneNumber, PhoneNumberFormat $format = PhoneNumberFormat::INTERNATIONAL): string
    {
        return $this->phoneNumberUtil->format($phoneNumber, $format);
    }

    /**
     * @return TwigTest[]
     */
    public function getTests(): array
    {
        return [
            new TwigTest('phone_number_of_type', [$this, 'isType']),
        ];
    }

    /**
     * @param PhoneNumber $phoneNumber phone number
     * @param PhoneNumberType $type phoneNumberType, or PhoneNumberType constant name
     *
     * @throws InvalidArgumentException if type argument is invalid
     */
    public function isType(PhoneNumber $phoneNumber, PhoneNumberType $type = PhoneNumberType::UNKNOWN): bool
    {
        return $this->phoneNumberUtil->getNumberType($phoneNumber) === $type;
    }

    public function getName(): string
    {
        return 'netbull_core.phone_number_extension';
    }
}
