<?php

namespace NetBull\CoreBundle\ORM\Objects;

class Point
{
    /**
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct(private float $latitude, private float $longitude)
    {
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!$this->getLatitude() || !$this->getLongitude()) {
            return '';
        }

        return $this->getLatitude() . ', ' . $this->getLongitude();
    }
}
