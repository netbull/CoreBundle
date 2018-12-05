<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

use NetBull\CoreBundle\Form\DataTransformer\RangeToStringTransformer;

/**
 * Class RangeType
 * @package NetBull\CoreBundle\Form\Type
 */
class RangeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('min', NumberType::class, [
                'required' => false,
            ])
            ->add('max', NumberType::class, [
                'required' => false,
            ])
            ->addViewTransformer(new RangeToStringTransformer())
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound' => true,
        ]);
    }
}
