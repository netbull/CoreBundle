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
    /**
     * @var PhoneNumberUtil
     */
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
     * @param PhoneNumber $phoneNumber Phone number.
     * @param PhoneNumberFormat $format Format, or format constant name.
     *
     * @return string Formatted phone number.
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
     * @param PhoneNumber $phoneNumber Phone number.
     * @param PhoneNumberType $type PhoneNumberType, or PhoneNumberType constant name.
     *
     * @return bool
     *
     * @throws InvalidArgumentException If type argument is invalid.
     */
    public function isType(PhoneNumber $phoneNumber, PhoneNumberType $type = PhoneNumberType::UNKNOWN): bool
    {
        return $this->phoneNumberUtil->getNumberType($phoneNumber) === $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'netbull_core.phone_number_extension';
    }
}
