<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType as BaseType;
use Symfony\Component\Form\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;
use NetBull\CoreBundle\Form\DataTransformer\MoneyToStringTransformer;

class MoneyType extends BaseType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $transformer = $options['localize'] ?
            new MoneyToLocalizedStringTransformer(
                $options['scale'],
                $options['grouping'],
                null,
                $options['divisor']
            ) :
            new MoneyToStringTransformer(
                $options['thousands_separator'],
                $options['decimal_separator'],
                $options['scale'],
                $options['grouping'],
                null,
                $options['divisor']
            );

        $builder->addViewTransformer($transformer);
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'localize' => false,
            'thousands_separator' => '.',
            'decimal_separator' => ',',
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'money';
    }
}
