<?php

namespace NetBull\CoreBundle\Utils;

/**
 * Class PDFLabelFormat
 * @package NetBull\CoreBundle\Utils
 */
class PDFLabelFormat
{
    const TYPE_STRING   = 0;
    const TYPE_INT      = 1;
    const TYPE_FLOAT    = 2;

    /**
     * Label Format fields stored in the 'value' field of the Option Value table.
     */
    private $defaults = [
        'paper-size' => [
            // Paper size: names defined in option_value table (option_group = 'paper_size')
            'name' => 'paper-size',
            'type' => self::TYPE_STRING,
            'default' => 'letter',
        ],
        'orientation' => [
            // Paper orientation: 'portrait' or 'landscape'
            'name' => 'orientation',
            'type' => self::TYPE_STRING,
            'default' => 'portrait',
        ],
        'font-name' => [
            // Font name: 'courier', 'helvetica', 'times'
            'name' => 'font-name',
            'type' => self::TYPE_STRING,
            'default' => 'helvetica',
        ],
        'font-size' => [
            // Font size: always in points
            'name' => 'font-size',
            'type' => self::TYPE_INT,
            'default' => 8,
        ],
        'font-style' => [
            // Font style: 'B' bold, 'I' italic, 'BI' bold+italic
            'name' => 'font-style',
            'type' => self::TYPE_STRING,
            'default' => '',
        ],
        'NX' => [
            // Number of labels horizontally
            'name' => 'NX',
            'type' => self::TYPE_INT,
            'default' => 3,
        ],
        'NY' => [
            // Number of labels vertically
            'name' => 'NY',
            'type' => self::TYPE_INT,
            'default' => 10,
        ],
        'metric' => [
            // Unit of measurement for all of the following fields
            'name' => 'metric',
            'type' => self::TYPE_STRING,
            'default' => 'mm',
        ],
        'lMargin' => [
            // Left margin
            'name' => 'lMargin',
            'type' => self::TYPE_FLOAT,
            'metric' => true,
            'default' => 4.7625,
        ],
        'tMargin' => [
            // Right margin
            'name' => 'tMargin',
            'type' => self::TYPE_FLOAT,
            'metric' => true,
            'default' => 12.7,
        ],
        'SpaceX' => [
            // Horizontal space between two labels
            'name' => 'SpaceX',
            'type' => self::TYPE_FLOAT,
            'metric' => true,
            'default' => 3.96875,
        ],
        'SpaceY' => [
            // Vertical space between two labels
            'name' => 'SpaceY',
            'type' => self::TYPE_FLOAT,
            'metric' => true,
            'default' => 0,
        ],
        'width' => [
            // Width of label
            'name' => 'width',
            'type' => self::TYPE_FLOAT,
            'metric' => true,
            'default' => 65.875,
        ],
        'height' => [
            // Height of label
            'name' => 'height',
            'type' => self::TYPE_FLOAT,
            'metric' => true,
            'default' => 25.4,
        ],
        'lPadding' => [
            // Space between text and left edge of label
            'name' => 'lPadding',
            'type' => self::TYPE_FLOAT,
            'metric' => true,
            'default' => 5.08,
        ],
        'tPadding' => [
            // Space between text and top edge of label
            'name' => 'tPadding',
            'type' => self::TYPE_FLOAT,
            'metric' => true,
            'default' => 5.08,
        ],
    ];

    /**
     * @var array
     */
    protected $values = [];

    /**
     * PDFLabelFormat constructor.
     * @param array $options
     */
    public function __construct( array $options = [])
    {
        if ( !empty($options) ) {
            $this->setOptions($options);
        }
    }

    /**
     * Set options
     * @param array $options
     */
    public function setOptions( array $options )
    {
        foreach ( $this->defaults as $option => $defaults ) {
            $this->values[$option] = ( array_key_exists($defaults['name'], $options) ) ? $options[$option] : $defaults['default'];
        }
    }

    /**
     * Get Label Format field from associative array.
     *
     * @param string        $field Name of a label format field.
     * @param null|string   $default
     * @return float|int|mixed|null
     */
    public function getValue( $field, $default = null ) {
        if ( array_key_exists($field, $this->defaults) ){
            switch ($this->defaults[$field]['type']) {
                case self::TYPE_INT:
                    return (int) $this->values[$field];
                case self::TYPE_FLOAT:
                    // Round float values to three decimal places and trim trailing zeros.
                    // Add a leading zero to values less than 1.
                    $f = sprintf('%05.3f', $this->values[$field]);
                    $f = rtrim($f, '0');
                    $f = rtrim($f, '.');
                    return (float) (empty($f) ? '0' : $f);
            }
            return $this->values[$field];
        }

        return $default;
    }

    /**
     * Check if field is metric
     * @param $field
     * @return bool
     */
    public function isMetric( $field ): bool
    {
        if ( array_key_exists($field, $this->defaults) ){
            return ( isset($this->defaults[$field]['metric']) ) ? $this->defaults[$field]['metric'] : false;
        }
        return false;
    }
}
