<?php

namespace NetBull\CoreBundle\Twig;

use NetBull\CoreBundle\Templating\Helper\PhoneNumberHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigTest;

class PhoneNumberExtension extends AbstractExtension
{
    /**
     * @var PhoneNumberHelper
     */
    protected PhoneNumberHelper $helper;

    /**
     * @param PhoneNumberHelper $helper
     */
    public function __construct(PhoneNumberHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return array(
            new TwigFilter('phone_number_format', [$this->helper, 'format']),
        );
    }

    /**
     * @return TwigTest[]
     */
    public function getTests(): array
    {
        return array(
            new TwigTest('phone_number_of_type', [$this->helper, 'isType']),
        );
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'phone_number_helper';
    }
}
