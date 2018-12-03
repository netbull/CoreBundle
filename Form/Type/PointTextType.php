<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use NetBull\CoreBundle\Form\DataTransformer\PointToStringTransformer;

/**
 * Class PointTextType
 * @package NetBull\CoreBundle\Form\Type
 */
class PointTextType extends TextType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new PointToStringTransformer());
    }
}
