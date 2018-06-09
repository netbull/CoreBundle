<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;

/**
 * Class NumberToStringTransformer
 * @package NetBull\CoreBundle\Form\DataTransformer
 */
class NumberToStringTransformer extends NumberToLocalizedStringTransformer
{
    protected $thousandsSeparator;
    protected $decimalSeparator;

    /**
     * NumberToStringTransformer constructor.
     * @param string $thousandsSeparator
     * @param string $decimalSeparator
     * @param int|null $scale
     * @param bool|null $grouping
     * @param int|null $roundingMode
     */
    public function __construct(string $thousandsSeparator = '.', string $decimalSeparator = ',', int $scale = null, ?bool $grouping = false, ?int $roundingMode = self::ROUND_HALF_UP)
    {
        parent::__construct($scale, $grouping, $roundingMode);

        $this->thousandsSeparator = $thousandsSeparator;
        $this->decimalSeparator = $decimalSeparator;
    }

    /**
     * Returns a preconfigured \NumberFormatter instance.
     *
     * @return \NumberFormatter
     */
    protected function getNumberFormatter()
    {
        $formatter = parent::getNumberFormatter();

        $formatter->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $this->thousandsSeparator);
        $formatter->setSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $this->decimalSeparator);
        $formatter->setPattern(sprintf('#%s##0%s###', $this->thousandsSeparator, $this->decimalSeparator));

        return $formatter;
    }
}
