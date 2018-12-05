<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

/**
 * Class CompoundRangeType
 * @package NetBull\CoreBundle\Form\Type
 */
class CompoundRangeType extends AbstractType implements DataTransformerInterface
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
            ->addViewTransformer($this)
        ;
    }

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        $parts = explode('-', $value);

        if (2 !== \count($parts)) {
            return [
                'min' => null,
                'max' => null,
            ];
        }

        return [
            'min' => $parts[0],
            'max' => $parts[1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform($value)
    {
        if (!$value || !\is_array($value) || 2 !== \count($value)) {
            return null;
        }

        return implode('-', $value);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'compound_range';
    }
}
