<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use NetBull\CoreBundle\Form\DataTransformer\EntityToPropertySimpleTransformer;

class EntityHiddenType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // attach the specified model transformer for this entity list field
        // this will convert data between object and string formats
        $builder->addModelTransformer(new EntityToPropertySimpleTransformer($this->em, $options['class']));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['class']);
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['object'] = $form->getData();
    }

    /**
     * @return string|null
     */
    public function getParent(): ?string
    {
        return HiddenType::class;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'entity_hidden';
    }
}
