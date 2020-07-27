<?php

namespace NetBull\CoreBundle\Utils;

/**
 * Class CRM_Utils_PDF_Utils
 * @package NetBull\CoreBundle\Utils
 */
class PDFUtils
{
    /**
     * convert value from one metric to another.
     *
     * @param $value
     * @param $from
     * @param $to
     * @param null $precision
     *
     * @return float|int
     */
    public static function convertMetric( $value, $from, $to, $precision = null ) {
        switch ($from . $to) {
            case 'incm':
                $value *= 2.54;
                break;
            case 'inmm':
                $value *= 25.4;
                break;
            case 'inpt':
                $value *= 72;
                break;
            case 'cmin':
                $value /= 2.54;
                break;
            case 'cmmm':
                $value *= 10;
                break;
            case 'cmpt':
                $value *= 72 / 2.54;
                break;
            case 'mmin':
                $value /= 25.4;
                break;
            case 'mmcm':
                $value /= 10;
                break;
            case 'mmpt':
                $value *= 72 / 25.4;
                break;
            case 'ptin':
                $value /= 72;
                break;
            case 'ptcm':
                $value *= 2.54 / 72;
                break;
            case 'ptmm':
                $value *= 25.4 / 72;
                break;
        }

        if ( !is_null($precision) ) {
            $value = round($value, $precision);
        }

        return $value;
    }
}
