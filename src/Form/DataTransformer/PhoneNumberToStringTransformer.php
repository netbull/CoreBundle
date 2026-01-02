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
     * @param string $defaultRegion
     * @param array $defaultRegions
     * @param PhoneNumberFormat $format
     */
    public function __construct(
        protected string $defaultRegion = PhoneNumberUtil::UNKNOWN_REGION,
        protected array $defaultRegions = [],
        protected PhoneNumberFormat $format = PhoneNumberFormat::INTERNATIONAL
    )
    {
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function transform(mixed $value): string
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
     * @return PhoneNumber|null
     */
    public function reverseTransform(mixed $value): ?PhoneNumber
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
