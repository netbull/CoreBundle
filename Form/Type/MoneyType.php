<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType as BaseType;
use Symfony\Component\Form\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;

use NetBull\CoreBundle\Form\DataTransformer\MoneyToStringTransformer;

/**
 * Class MoneyType
 * @package NetBull\CoreBundle\Form\Type
 */
class MoneyType extends BaseType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'localize' => false,
            'thousands_separator' => '.',
            'decimal_separator' => ',',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'money';
    }
}
