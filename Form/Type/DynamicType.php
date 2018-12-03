<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use NetBull\CoreBundle\Form\DataTransformer\EntityToPropertyTransformer;
use NetBull\CoreBundle\Form\DataTransformer\EntitiesToPropertyTransformer;

/**
 * Class DynamicType
 * @package NetBull\CoreBundle\Form\Type
 */
class DynamicType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * DynamicType constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // add custom data transformer
        if ($options['transformer']) {
            if (!is_string($options['transformer'])) {
                throw new \Exception('The option transformer must be a string');
            }
            if (!class_exists($options['transformer'])) {
                throw new \Exception('Unable to load class: '.$options['transformer']);
            }
            $transformer = new $options['transformer']($this->em, $options['class']);
            if (!$transformer instanceof DataTransformerInterface) {
                throw new \Exception(sprintf('The custom transformer %s must implement %s', get_class($transformer), DataTransformerInterface::class));
            }
            // add the default data transformer
        } else {
            $transformer = $options['multiple']
                ? new EntitiesToPropertyTransformer($this->em, $options['class'], $options['text_property'], $options['primary_key'])
                : new EntityToPropertyTransformer($this->em, $options['class'], $options['text_property'], $options['primary_key'], $options['data-attr']);
        }
        $builder->addViewTransformer($transformer, true);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        $varNames = ['multiple', 'primary_key', 'hidden'];

        foreach ($varNames as $varName) {
            $view->vars[$varName] = $options[$varName];
        }

        if ($options['multiple']) {
            $view->vars['full_name'] .= '[]';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => null,
            'primary_key' => 'id',
            'hidden' => false,
            'multiple' => false,
            'compound' => false,
            'text_property' => null,
            'required' => false,
            'transformer' => null,
            'data-attr' => []
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'dynamic_type';
    }
}
