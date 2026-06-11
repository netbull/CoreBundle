<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use NetBull\CoreBundle\ORM\Objects\Point;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PointToStringTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if (!(string) $value) {
            return $value;
        }

        return $value->getLatitude() . ', ' . $value->getLongitude();
    }

    public function reverseTransform(mixed $value): ?Point
    {
        if (!$value) {
            return null;
        }

        if (is_array($value)) {
            $value = $value['gpsCoordinate'];
        }

        $coordinates = explode(', ', $value);

        if (2 !== count($coordinates)) {
            throw new TransformationFailedException('The Coordinates should contain latitude and longitude!');
        }

        return new Point($coordinates[0], $coordinates[1]);
    }
}
