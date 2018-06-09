<?php

namespace NetBull\CoreBundle\Utils;

use Knp\DoctrineBehaviors\Model\Sluggable\Transliterator;

/**
 * Class SlugGenerator
 * @package NetBull\CoreBundle\Utils
 */
class SlugGenerator
{
    /**
     * @param array $values
     * @param string $delimiter
     * @return mixed|string
     */
    static public function generate (array $values, $delimiter = "-")
    {
        $usableValues = [];
        foreach ($values as $fieldName => $fieldValue) {
            if (!empty($fieldValue)) {
                $usableValues[] = $fieldValue;
            }
        }

        if (count($usableValues) < 1) {
            throw new \UnexpectedValueException(
                'Sluggable expects to have at least one usable (non-empty) field from the following: [ ' . implode(array_keys($values), ',') .' ]'
            );
        }

        // generate the slug itself
        $sluggableText = implode(' ', $usableValues);

        $transliterator = new Transliterator();
        $sluggableText = $transliterator->transliterate($sluggableText, $delimiter);

        $urlized = strtolower( trim( preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $sluggableText ), $delimiter ) );
        $urlized = preg_replace("/[\/_|+ -]+/", $delimiter, $urlized);

        return $urlized;
    }
}
