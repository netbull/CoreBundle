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
     * @param int|string $format Format, or format constant name.
     *
     * @return string Formatted phone number.
     *
     * @throws InvalidArgumentException If an argument is invalid.
     */
    public function format(PhoneNumber $phoneNumber, int|string $format = PhoneNumberFormat::INTERNATIONAL): string
    {
        if (true === is_string($format)) {
            $constant = '\libphonenumber\PhoneNumberFormat::' . $format;

            if (false === defined($constant)) {
                throw new InvalidArgumentException('The format must be either a constant value or name in libphonenumber\PhoneNumberFormat');
            }

            $format = constant('\libphonenumber\PhoneNumberFormat::' . $format);
        }

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
     * @param int|string $type PhoneNumberType, or PhoneNumberType constant name.
     *
     * @return bool
     *
     * @throws InvalidArgumentException If type argument is invalid.
     */
    public function isType(PhoneNumber $phoneNumber, int|string $type = PhoneNumberType::UNKNOWN): bool
    {
        if (true === is_string($type)) {
            $constant = '\libphonenumber\PhoneNumberType::' . $type;

            if (false === defined($constant)) {
                throw new InvalidArgumentException('The format must be either a constant value or name in libphonenumber\PhoneNumberType');
            }

            $type = constant('\libphonenumber\PhoneNumberType::' . $type);
        }

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
