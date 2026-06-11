<?php

namespace NetBull\CoreBundle\ORM\Objects;

class Range
{
    public function __construct(private int $min, private int $max)
    {
    }

    public function getMin(): int
    {
        return $this->min;
    }

    public function setMin(int $min): Range
    {
        $this->min = $min;

        return $this;
    }

    public function getMax(): int
    {
        return $this->max;
    }

    public function setMax(int $max): Range
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->getMin() || $this->getMax()) {
            return '';
        }

        return $this->getMin() . '-' . $this->getMax();
    }
}
