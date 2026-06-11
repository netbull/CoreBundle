<?php

namespace NetBull\CoreBundle\Form\Type;

use NetBull\CoreBundle\Form\DataTransformer\PointToStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PointTextType extends TextType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(new PointToStringTransformer());
    }
}
