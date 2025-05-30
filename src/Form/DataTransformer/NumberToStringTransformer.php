<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use NumberFormatter;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;

class NumberToStringTransformer extends NumberToLocalizedStringTransformer
{
    /**
     * @param string $thousandsSeparator
     * @param string $decimalSeparator
     * @param int|null $scale
     * @param bool|null $grouping
     * @param int|null $roundingMode
     */
    public function __construct(protected string $thousandsSeparator = '.', protected string $decimalSeparator = ',', int $scale = null, ?bool $grouping = false, ?int $roundingMode = PHP_ROUND_HALF_UP)
    {
        parent::__construct($scale, $grouping, $roundingMode);
    }

    /**
     * Returns a preconfigured \NumberFormatter instance.
     *
     * @return NumberFormatter
     */
    protected function getNumberFormatter(): NumberFormatter
    {
        $formatter = parent::getNumberFormatter();

        $formatter->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $this->thousandsSeparator);
        $formatter->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $this->decimalSeparator);
        $formatter->setPattern(sprintf('#%s##0%s###', $this->thousandsSeparator, $this->decimalSeparator));

        return $formatter;
    }
}
