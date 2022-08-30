<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use NetBull\CoreBundle\Form\DataTransformer\PointToStringTransformer;

class PointTextType extends TextType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new PointToStringTransformer());
    }
}
