<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use NetBull\CoreBundle\Form\DataTransformer\PointToStringTransformer;

/**
 * Class PointType
 * @package NetBull\CoreBundle\Form\Type
 */
class PointType extends HiddenType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new PointToStringTransformer());
    }
}
