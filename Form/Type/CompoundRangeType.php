<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function validateRange($value, ExecutionContextInterface $context)
    {
        $value = $this->transform($value);

        if (null === $value) {
            return;
        }

        if (empty($value['min']) || empty($value['max'])) {
            $context
                ->buildViolation('Both values "Min" and "Max" should be entered')
                ->addViolation();
        }

        $min = intval($value['min']);
        $max = intval($value['max']);

        if ($min > $max) {
            $context
                ->buildViolation('The max value has to be higher than the min value')
                ->addViolation();
        }
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

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound' => true,
            'constraints' => new Callback([$this, 'validateRange']),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'compound_range';
    }
}
