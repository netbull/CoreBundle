<?php

namespace NetBull\CoreBundle\ORM\Objects;

class Point
{
    /**
     * @var float
     */
    private float $latitude;

    /**
     * @var float
     */
    private float $longitude;

    /**
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
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
