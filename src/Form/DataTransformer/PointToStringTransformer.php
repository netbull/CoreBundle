<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use NetBull\CoreBundle\ORM\Objects\Point;

class PointToStringTransformer implements DataTransformerInterface
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function transform(mixed $value): mixed
    {
        if (!(string)$value) {
            return $value;
        }

        return $value->getLatitude() . ', ' . $value->getLongitude();
    }

    /**
     * Transforms a string to an object.
     * @param mixed $value
     * @return mixed
     */
    public function reverseTransform(mixed $value): mixed
    {
        if (!$value) {
            return null;
        }

        if (is_array($value)) {
            $value = $value['gpsCoordinate'];
        }

        $coordinates = explode(', ', $value);

        if (count($coordinates) !== 2) {
            throw new TransformationFailedException(sprintf(
                'The Coordinates should contain latitude and longitude!',
                $value
            ));
        }

        return new Point($coordinates[0], $coordinates[1]);
    }
}
