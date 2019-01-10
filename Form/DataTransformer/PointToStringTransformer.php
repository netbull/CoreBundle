<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use NetBull\CoreBundle\ORM\Objects\Point;

/**
 * Class PointToStringTransformer
 * @package NetBull\CoreBundle\Form\DataTransformer
 */
class PointToStringTransformer implements DataTransformerInterface
{
    /**
     * @param Point $point
     * @return string
     */
    public function transform($point)
    {
        if (!(string)$point) {
            return $point;
        }

        return $point->getLatitude() . ', ' . $point->getLongitude();
    }

    /**
     * Transforms a string to an object.
     * @param mixed $stringPoint
     * @return null|string
     */
    public function reverseTransform($stringPoint)
    {
        if (!$stringPoint) {
            return null;
        }

        if (null === $stringPoint) {
            throw new TransformationFailedException(sprintf(
                'An area with number "%s" does not exist!',
                $stringPoint
            ));
        }

        if (is_array($stringPoint)) {
            $stringPoint = $stringPoint['gpsCoordinate'];
        }

        $coordinates = explode(', ', $stringPoint);

        if (count($coordinates) !== 2) {
            throw new TransformationFailedException(sprintf(
                'The Coordinates should contain latitude and longitude!',
                $stringPoint
            ));
        }

        return new Point($coordinates[0], $coordinates[1]);
    }
}
