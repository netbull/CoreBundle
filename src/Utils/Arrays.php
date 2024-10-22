<?php

namespace NetBull\CoreBundle\Utils;

class Arrays
{
    /**
     * @param string $needle
     * @param array $haystack
     * @return bool|int|string
     */
    public static function arraySearchRecursive(string $needle, array $haystack = []): bool|int|string
    {
        foreach ($haystack as $key => $value) {
            if ($needle === $value) {
                return $key;
            } else if ((is_array($value) && $key = array_search($needle, $value))) {
                return $key;
            } else if (is_array($value)) {
                self::arraySearchRecursive($needle, $value);
            }
        }
        return false;
    }
}
