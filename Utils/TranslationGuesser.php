<?php

namespace NetBull\CoreBundle\Utils;

/**
 * Class TranslationGuesser
 * @package NetBull\CoreBundle\Utils
 */
class TranslationGuesser
{
    /**
     * Guess the translation
     * @param array     $array
     * @param string    $field
     * @param string    $locale
     * @param bool      $strict
     * @return mixed
     */
    static function guess(array $array, $field, $locale = 'en', $strict = false)
    {
        if (empty($array)) {
            return '';
        }

        if (array_keys($array) !== range(0, count($array) - 1)) {
            if (isset($array[$locale])) {
                return $array[$locale][$field];
            } else if (!$strict) {
                if (isset($array['en'])) {
                    return $array['en'][$field];
                } else {
                    return array_values($array)[0][$field];
                }
            }
        } else {
            $tmp = null;
            foreach ($array as $arr) {
                if (isset($arr['locale']) && $arr['locale'] == $locale) {
                    $tmp = $arr;
                }
            }

            if (isset($tmp[$field])) {
                return $tmp[$field];
            }
        }

        return '';
    }

    /**
     * Get the translation
     * @param array     $array
     * @param string    $locale
     * @param bool      $strict
     * @return mixed
     */
    static function get(array $array, $locale = 'en', $strict = false)
    {
        if (empty($array)) {
            return '';
        }

        if (isset($array[$locale])) {
            return $array[$locale];
        } else if (!$strict) {
            if (isset($array['en'])) {
                return $array['en'];
            } else {
                return array_values($array)[0];
            }
        }

        return false;
    }
}
