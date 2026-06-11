<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PhoneNumberToArrayTransformer implements DataTransformerInterface
{
    public function __construct(protected array $countryChoices)
    {
    }

    /**
     * @return array|string[]
     */
    public function transform(mixed $value): array
    {
        if (null === $value) {
            return [
                'country' => '',
                'number' => '',
            ];
        } elseif (false === $value instanceof PhoneNumber) {
            throw new TransformationFailedException('Expected a \libphonenumber\PhoneNumber.');
        }

        $util = PhoneNumberUtil::getInstance();

        if (false === in_array($util->getRegionCodeForNumber($value), $this->countryChoices)) {
            throw new TransformationFailedException('Invalid country.');
        }

        return [
            'country' => $util->getRegionCodeForNumber($value),
            'number' => $util->format($value, PhoneNumberFormat::NATIONAL),
        ];
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (!$value) {
            return null;
        }

        if (!is_array($value)) {
            throw new TransformationFailedException('Expected an array.');
        }

        if ('' === trim($value['number'])) {
            return null;
        }

        $util = PhoneNumberUtil::getInstance();

        try {
            $phoneNumber = $util->parse($value['number'], $value['country']);
        } catch (NumberParseException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }

        if (false === in_array($util->getRegionCodeForNumber($phoneNumber), $this->countryChoices)) {
            throw new TransformationFailedException('Invalid country.');
        }

        return $phoneNumber;
    }
}
