<?php

namespace NetBull\CoreBundle\Twig;

use NetBull\CoreBundle\Templating\Helper\PhoneNumberHelper;

/**
 * Class PhoneNumberExtension
 * @package NetBull\CoreBundle\Twig
 */
class PhoneNumberExtension extends \Twig_Extension
{
    /**
     * Phone number helper.
     *
     * @var PhoneNumberHelper
     */
    protected $helper;

    /**
     * Constructor.
     *
     * @param PhoneNumberHelper $helper Phone number helper.
     */
    public function __construct(PhoneNumberHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('phone_number_format', array($this->helper, 'format')),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('phone_number_of_type', array($this->helper, 'isType')),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'phone_number_helper';
    }
}
