<?php

namespace NetBull\CoreBundle\ORM\Objects;

class Range
{
    /**
     * @param int $min
     * @param int $max
     */
    public function __construct(private int $min, private int $max)
    {
    }

    /**
     * @return int
     */
    public function getMin(): int
    {
        return $this->min;
    }

    /**
     * @param int $min
     * @return Range
     */
    public function setMin(int $min): Range
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return int
     */
    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * @param int $max
     * @return Range
     */
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
