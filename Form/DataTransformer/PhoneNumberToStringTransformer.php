<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class PhoneNumberToStringTransformer
 * @package NetBull\CoreBundle\Form\DataTransformer
 */
class PhoneNumberToStringTransformer implements DataTransformerInterface
{
    /**
     * Default region code.
     *
     * @var string
     */
    private $defaultRegion;

    /**
     * Display format.
     *
     * @var int
     */
    private $format;

    /**
     * Constructor.
     *
     * @param string $defaultRegion Default region code.
     * @param int    $format        Display format.
     */
    public function __construct($defaultRegion = PhoneNumberUtil::UNKNOWN_REGION, $format = PhoneNumberFormat::INTERNATIONAL)
    {
        $this->defaultRegion = $defaultRegion;
        $this->format = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($phoneNumber)
    {
        if (null === $phoneNumber) {
            return '';
        } elseif (false === $phoneNumber instanceof PhoneNumber) {
            throw new TransformationFailedException('Expected a \libphonenumber\PhoneNumber.');
        }

        $util = PhoneNumberUtil::getInstance();

        if (PhoneNumberFormat::NATIONAL === $this->format) {
            return $util->formatOutOfCountryCallingNumber($phoneNumber, $this->defaultRegion);
        }

        return $util->format($phoneNumber, $this->format);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($string)
    {
        if (!$string && $string !== '0') {
            return null;
        }

        $util = PhoneNumberUtil::getInstance();

        try {
            return $util->parse($string, $this->defaultRegion);
        } catch (NumberParseException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
