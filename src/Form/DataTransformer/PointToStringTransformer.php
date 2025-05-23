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
     * @param mixed $value
     * @return Point|null
     */
    public function reverseTransform(mixed $value): ?Point
    {
        if (!$value) {
            return null;
        }

        if (is_array($value)) {
            $value = $value['gpsCoordinate'];
        }

        $coordinates = explode(', ', $value);

        if (count($coordinates) !== 2) {
            throw new TransformationFailedException('The Coordinates should contain latitude and longitude!');
        }

        return new Point($coordinates[0], $coordinates[1]);
    }
}
