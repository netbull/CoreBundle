<?php

namespace NetBull\CoreBundle\Templating\Helper;

use InvalidArgumentException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use Symfony\Component\Templating\Helper\HelperInterface;

class PhoneNumberHelper implements HelperInterface
{
    /**
     * @var PhoneNumberUtil
     */
    protected PhoneNumberUtil $phoneNumberUtil;

    /**
     * @var string
     */
    protected string $charset = 'UTF-8';

    public function __construct()
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
    }

    /**
     * @param $charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'phone_number_helper';
    }

    /**
     * Format a phone number.
     *
     * @param PhoneNumber $phoneNumber Phone number.
     * @param int|string  $format      Format, or format constant name.
     *
     * @return string Formatted phone number.
     *
     * @throws InvalidArgumentException If an argument is invalid.
     */
    public function format(PhoneNumber $phoneNumber, $format = PhoneNumberFormat::INTERNATIONAL): string
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
     * @param PhoneNumber $phoneNumber Phone number.
     * @param int|string  $type      PhoneNumberType, or PhoneNumberType constant name.
     *
     * @return bool
     *
     * @throws InvalidArgumentException If type argument is invalid.
     */
    public function isType(PhoneNumber $phoneNumber, $type = PhoneNumberType::UNKNOWN): bool
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
}
