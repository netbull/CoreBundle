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
     * Default region codes.
     *
     * @var array
     */
    private $defaultRegions;

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
     * @param array $defaultRegions
     * @param int $format Display format.
     */
    public function __construct($defaultRegion = PhoneNumberUtil::UNKNOWN_REGION, $defaultRegions = [], $format = PhoneNumberFormat::INTERNATIONAL)
    {
        $this->defaultRegion = $defaultRegion;
        $this->defaultRegions = $defaultRegions;
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

        if (!empty($constraint->defaultRegions)) {
            $exception = null;
            foreach ($constraint->defaultRegions as $defaultRegion) {
                try {
                    return $util->parse($string, $defaultRegion);
                } catch (NumberParseException $e) {
                    $exception = new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
                }
            }

            if ($exception) {
                throw $exception;
            }
        } else {
            try {
                return $util->parse($string, $this->defaultRegion);
            } catch (NumberParseException $e) {
                throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return null;
    }
}
