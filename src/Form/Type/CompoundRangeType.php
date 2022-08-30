<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CompoundRangeType extends AbstractType implements DataTransformerInterface
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('min', IntegerType::class, [
            'required' => false,
        ])
        ->add('max', IntegerType::class, [
            'required' => false,
        ])
        ->addViewTransformer($this);
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

        if ((empty($value['min']) && 0 !== $value['min']) || (empty($value['max']) && 0 !== $value['max'])) {
            $context->buildViolation('Both values "Min" and "Max" should be entered')
                ->addViolation();
        }

        $min = intval($value['min']);
        $max = intval($value['max']);

        if ($min > $max) {
            $context->buildViolation('The max value has to be higher than the min value')
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

        if (2 !== count($parts)) {
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
     * @param $value
     * @return string|null
     */
    public function reverseTransform($value): ?string
    {
        if (!$value || !is_array($value) || 2 !== count($value)) {
            return null;
        }

        return implode('-', $value);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound' => true,
            'constraints' => new Callback([$this, 'validateRange']),
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'compound_range';
    }
}
