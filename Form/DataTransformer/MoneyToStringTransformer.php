<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class MoneyToStringTransformer
 * @package NetBull\CoreBundle\Form\DataTransformer
 */
class MoneyToStringTransformer extends NumberToStringTransformer
{
    private $divisor;

    /**
     * MoneyToStringTransformer constructor.
     * @param string $thousandsSeparator
     * @param string $decimalSeparator
     * @param int|null $scale
     * @param bool|null $grouping
     * @param int|null $roundingMode
     * @param int|null $divisor
     */
    public function __construct(string $thousandsSeparator = '.', string $decimalSeparator = ',', ?int $scale = 2, ?bool $grouping = true, ?int $roundingMode = self::ROUND_HALF_UP, ?int $divisor = 1)
    {
        if (null === $grouping) {
            $grouping = true;
        }

        if (null === $scale) {
            $scale = 2;
        }

        parent::__construct($thousandsSeparator, $decimalSeparator, $scale, $grouping, $roundingMode);

        if (null === $divisor) {
            $divisor = 1;
        }

        $this->divisor = $divisor;
    }

    /**
     * Transforms a normalized format into a localized money string.
     *
     * @param int|float $value Normalized number
     *
     * @return string Localized money string
     *
     * @throws TransformationFailedException if the given value is not numeric or
     *                                       if the value can not be transformed
     */
    public function transform($value)
    {
        if (null !== $value && 1 !== $this->divisor) {
            if (!is_numeric($value)) {
                throw new TransformationFailedException('Expected a numeric.');
            }
            $value = (string) ($value / $this->divisor);
        }

        return parent::transform($value);
    }

    /**
     * Transforms a localized money string into a normalized format.
     *
     * @param string $value Localized money string
     *
     * @return int|float Normalized number
     *
     * @throws TransformationFailedException if the given value is not a string
     *                                       or if the value can not be transformed
     */
    public function reverseTransform($value)
    {
        $value = parent::reverseTransform($value);

        if (null !== $value && 1 !== $this->divisor) {
            $value = (float) (string) ($value * $this->divisor);
        }

        return $value;
    }
}
