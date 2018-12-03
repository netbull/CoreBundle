<?php

namespace NetBull\CoreBundle\Templating\Helper;

use InvalidArgumentException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use Symfony\Component\Templating\Helper\HelperInterface;

/**
 * Class PhoneNumberHelper
 * @package NetBull\CoreBundle\Templating\Helper
 */
class PhoneNumberHelper implements HelperInterface
{
    /**
     * Phone number utility.
     *
     * @var PhoneNumberUtil
     */
    protected $phoneNumberUtil;

    /**
     * Charset.
     *
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * PhoneNumberHelper constructor.
     */
    public function __construct()
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * {@inheritdoc}
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
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
    public function format(PhoneNumber $phoneNumber, $format = PhoneNumberFormat::INTERNATIONAL)
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
    public function isType(PhoneNumber $phoneNumber, $type = PhoneNumberType::UNKNOWN)
    {
        if (true === is_string($type)) {
            $constant = '\libphonenumber\PhoneNumberType::' . $type;

            if (false === defined($constant)) {
                throw new InvalidArgumentException('The format must be either a constant value or name in libphonenumber\PhoneNumberType');
            }

            $type = constant('\libphonenumber\PhoneNumberType::' . $type);
        }

        return $this->phoneNumberUtil->getNumberType($phoneNumber) === $type ? true : false;
    }
}
