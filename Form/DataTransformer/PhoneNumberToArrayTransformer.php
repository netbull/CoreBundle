<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

/**
 * Class PhoneNumberToArrayTransformer
 * @package NetBull\CoreBundle\Form\DataTransformer
 */
class PhoneNumberToArrayTransformer implements DataTransformerInterface
{
    /**
     * @var array
     */
    private $countryChoices;

    /**
     * Constructor.
     *
     * @param array $countryChoices
     */
    public function __construct(array $countryChoices)
    {
        $this->countryChoices = $countryChoices;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($phoneNumber)
    {
        if (null === $phoneNumber) {
            return array('country' => '', 'number' => '');
        } elseif (false === $phoneNumber instanceof PhoneNumber) {
            throw new TransformationFailedException('Expected a \libphonenumber\PhoneNumber.');
        }

        $util = PhoneNumberUtil::getInstance();

        if (false === in_array($util->getRegionCodeForNumber($phoneNumber), $this->countryChoices)) {
            throw new TransformationFailedException('Invalid country.');
        }

        return array(
            'country' => $util->getRegionCodeForNumber($phoneNumber),
            'number' => $util->format($phoneNumber, PhoneNumberFormat::NATIONAL),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
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
