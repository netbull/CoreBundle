<?php

namespace NetBull\CoreBundle\Utils;

/**
 * Class PrintLabels
 * @package NetBull\CoreBundle\Utils
 */
class PrintLabels extends \TCPDF
{
    /**
     * @var PDFLabelFormat
     */
    public $format;

    // Left margin of labels
    public $marginLeft;

    // Top margin of labels
    public $marginTop;

    // Horizontal space between 2 labels
    public $xSpace;

    // Vertical space between 2 labels
    public $ySpace;

    // Number of labels horizontally
    public $xNumber;

    // Number of labels vertically
    public $yNumber;

    // Width of label
    public $width;

    // Height of label
    public $height;

    // Line Height of label - used in event code
    public $lineHeight = 0;

    // Space between text and left edge of label
    public $paddingLeft;

    // Space between text and top edge of label
    public $paddingTop;

    // Character size (in points)
    public $charSize;

    // Metric used for all PDF doc measurements
    public $metricDoc;

    // Name of the font
    public $fontName;

    // 'B' bold, 'I' italic, 'BI' bold+italic
    public $fontStyle;

    // Paper size name
    public $paperSize;

    // Paper orientation
    public $orientation;

    // Paper dimensions array (w, h)
    public $paper_dimensions;

    // Counter for positioning labels
    public $countX = 0;

    // Counter for positioning labels
    public $countY = 0;

    /**
     * @var array
     */
    private $formats = [
        'Labels'        => [ 'paper-size' => 'A4', 'orientation' => 'portrait', 'font-name' => 'helvetica', 'font-size' => 9, 'font-style' => '', 'NX' => 5, 'NY' => 13, 'metric'=>'mm', 'lMargin' => 5, 'tMargin' => 10.5, 'SpaceX' => 3, 'SpaceY' => .32, 'width' => 38, 'height' => 21, 'lPadding' => 0, 'tPadding' => 0 ],
        'OrderLabels'   => [ 'paper-size' => 'A4', 'orientation' => 'portrait', 'font-name' => 'helvetica', 'font-size' => 12, 'font-style' => '', 'NX' => 3, 'NY' => 7, 'metric'=>'mm', 'lMargin' => 5, 'tMargin' => 5, 'SpaceX' => 0, 'SpaceY' => .32, 'width' => 70, 'height' => 42, 'lPadding' => 0, 'tPadding' => 0 ]
    ];

    /**
     * Constructor.
     *
     * @param array|string  $format Either the name of a Label Format in the Option Value table. or an array of Label Format values.
     * @param string        $unit Unit of measure for the PDF document
     */
    public function __construct( $format, $unit = 'mm' )
    {
        if ( is_array($format) ) {
            // Custom format
            $tFormat = new PDFLabelFormat($format);
        } else {
            // Saved format
            if ( !array_key_exists($format, $this->formats) ) {
                $this->Error('The format is unknown');
            }
            $tFormat = new PDFLabelFormat($this->formats[$format]);
        }

        $this->LabelSetFormat($tFormat, $unit);

        parent::__construct($this->orientation, $this->metricDoc, $this->paper_dimensions);
        $this->SetFont($this->fontName, $this->fontStyle);
        $this->SetFontSize($this->charSize);
        $this->SetAutoPageBreak(false);
        $this->SetHeaderMargin(0);
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
    }

    /**
     * @param string $name
     * @param bool $convert
     *
     * @return float|int|mixed
     */
    public function getFormatValue( $name, $convert = false )
    {
        $value = $this->format->getValue($name);

        if ( $convert) {
            $value = PDFUtils::convertMetric($value, 'mm', $this->metricDoc);
        }

        return $value;
    }

    /**
     * initialize label format settings.
     *
     * @param $format
     * @param $unit
     */
    public function LabelSetFormat( $format, $unit )
    {
        $this->format = $format;
        $this->paperSize = $this->getFormatValue('paper-size');
        $this->orientation = $this->getFormatValue('orientation');
        $this->fontName = $this->getFormatValue('font-name');
        $this->charSize = $this->getFormatValue('font-size');
        $this->fontStyle = $this->getFormatValue('font-style');
        $this->xNumber = $this->getFormatValue('NX');
        $this->yNumber = $this->getFormatValue('NY');
        $this->metricDoc = $unit;
        $this->marginLeft = $this->getFormatValue('lMargin', true);
        $this->marginTop = $this->getFormatValue('tMargin', true);
        $this->xSpace = $this->getFormatValue('SpaceX', true);
        $this->ySpace = $this->getFormatValue('SpaceY', true);
        $this->width = $this->getFormatValue('width', true);
        $this->height = $this->getFormatValue('height', true);
        $this->paddingLeft = $this->getFormatValue('lPadding', true);
        $this->paddingTop = $this->getFormatValue('tPadding', true);
        $this->paper_dimensions = 'A4';
    }

    /**
     * Generate the pdf of one label (can be modified using SetGenerator)
     *
     * @param string $text
     */
    public function generateLabel( string $text ) {
        $args = [
            'w'             => $this->width,
            'h'             => 0,
            'txt'           => $text,
            'border'        => 0,
            'align'         => 'L',
            'fill'          => 0,
            'ln'            => 0,
            'x'             => '',
            'y'             => '',
            'reseth'        => true,
            'stretch'       => 0,
            'ishtml'        => false,
            'autopadding'   => false,
            'maxh'          => $this->height,
        ];

        if ($args['ishtml'] == true) {
            $this->writeHTMLCell($args['w'], $args['h'],
                $args['x'], $args['y'],
                $args['txt'], $args['border'],
                $args['ln'], $args['fill'],
                $args['reseth'], $args['align'],
                $args['autopadding']
            );
        } else {
            $this->multiCell($args['w'], $args['h'],
                $args['txt'], $args['border'],
                $args['align'], $args['fill'],
                $args['ln'], $args['x'],
                $args['y'], $args['reseth'],
                $args['stretch'], $args['ishtml'],
                $args['autopadding'], $args['maxh']
            );
        }
    }
    /**
     * Print a label.
     *
     * @param string $text
     */
    public function AddPdfLabel( string $text )
    {
        if ( $this->countX == $this->xNumber ) {
            // Page full, we start a new one
            $this->AddPage();
            $this->countX = 0;
            $this->countY = 0;
        }

        $posX = $this->marginLeft + ($this->countX * ($this->width + $this->xSpace));
        $posY = $this->marginTop + ($this->countY * ($this->height + $this->ySpace));

        $this->SetXY($posX + $this->paddingLeft, $posY + $this->paddingTop);

        $this->generateLabel($text);

        $this->countY++;
        if ( $this->countY == $this->yNumber ) {
            // End of column reached, we start a new one
            $this->countX++;
            $this->countY = 0;
        }
    }
}
