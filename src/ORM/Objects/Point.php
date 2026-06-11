<?php

namespace NetBull\CoreBundle\ORM\Objects;

class Point
{
    public function __construct(private float $latitude, private float $longitude)
    {
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

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
