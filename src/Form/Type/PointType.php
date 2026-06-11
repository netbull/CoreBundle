<?php

namespace NetBull\CoreBundle\Form\Type;

use NetBull\CoreBundle\Form\DataTransformer\PointToStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class PointType extends HiddenType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(new PointToStringTransformer());
    }
}
