<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use NetBull\CoreBundle\ORM\Objects\Range;

/**
 * Class RangeToStringTransformer
 * @package NetBull\CoreBundle\Form\DataTransformer
 */
class RangeToStringTransformer implements DataTransformerInterface
{
    /**
     * @param Range|null $range
     * @return mixed|string
     */
    public function transform($range)
    {
        if (!$range) {
            return $range;
        }

        return (string)$range;
    }

    /**
     * Transforms a string to an object.
     * @param mixed $stringRange
     * @return null|string
     */
    public function reverseTransform($stringRange)
    {
        if (!$stringRange) {
            return null;
        }

        if (null === $stringRange) {
            throw new TransformationFailedException(sprintf(
                'An area with number "%s" does not exist!',
                $stringRange
            ));
        }

        $range = explode('-', $stringRange);

        if (2 !== count($range)) {
            throw new TransformationFailedException(sprintf(
                'The Range should contain min and max value!',
                $stringRange
            ));
        }

        return new Range($range[0], $range[1]);
    }
}
