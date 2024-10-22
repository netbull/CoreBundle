<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PhoneNumberToStringTransformer implements DataTransformerInterface
{
    /**
     * Default region code.
     *
     * @var string
     */
    private string $defaultRegion;

    /**
     * Default region codes.
     *
     * @var array
     */
    private array $defaultRegions;

    /**
     * Display format.
     *
     * @var int
     */
    private int $format;

    /**
     * @param string $defaultRegion
     * @param array $defaultRegions
     * @param int $format
     */
    public function __construct(string $defaultRegion = PhoneNumberUtil::UNKNOWN_REGION, array $defaultRegions = [], int $format = PhoneNumberFormat::INTERNATIONAL)
    {
        $this->defaultRegion = $defaultRegion;
        $this->defaultRegions = $defaultRegions;
        $this->format = $format;
    }

    /**
     * @param mixed $value
     * @return mixed|string|null
     */
    public function transform(mixed $value): mixed
    {
        if (null === $value) {
            return '';
        } elseif (false === $value instanceof PhoneNumber) {
            throw new TransformationFailedException('Expected a \libphonenumber\PhoneNumber.');
        }

        $util = PhoneNumberUtil::getInstance();

        if (PhoneNumberFormat::NATIONAL === $this->format) {
            return $util->formatOutOfCountryCallingNumber($value, $this->defaultRegion);
        }

        return $util->format($value, $this->format);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function reverseTransform(mixed $value): mixed
    {
        if (!$value && '0' !== $value) {
            return null;
        }

        $util = PhoneNumberUtil::getInstance();

        if (!empty($constraint->defaultRegions)) {
            $exception = null;
            foreach ($constraint->defaultRegions as $defaultRegion) {
                try {
                    return $util->parse($value, $defaultRegion);
                } catch (NumberParseException $e) {
                    $exception = new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
                }
            }

            if ($exception) {
                throw $exception;
            }
        } else {
            try {
                return $util->parse($value, $this->defaultRegion);
            } catch (NumberParseException $e) {
                throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return null;
    }
}
