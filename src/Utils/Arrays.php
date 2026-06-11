<?php

namespace NetBull\CoreBundle\Utils;

class Arrays
{
    public static function arraySearchRecursive(string $needle, array $haystack = []): bool|int|string
    {
        foreach ($haystack as $key => $value) {
            if ($needle === $value) {
                return $key;
            } elseif (is_array($value) && $key = array_search($needle, $value)) {
                return $key;
            } elseif (is_array($value)) {
                self::arraySearchRecursive($needle, $value);
            }
        }

        return false;
    }
}
