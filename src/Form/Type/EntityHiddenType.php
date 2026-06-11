<?php

namespace NetBull\CoreBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use NetBull\CoreBundle\Form\DataTransformer\EntityToPropertySimpleTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityHiddenType extends AbstractType
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // attach the specified model transformer for this entity list field
        // this will convert data between object and string formats
        $builder->addModelTransformer(new EntityToPropertySimpleTransformer($this->em, $options['class']));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['class']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['object'] = $form->getData();
    }

    public function getParent(): ?string
    {
        return HiddenType::class;
    }

    public function getName(): string
    {
        return 'entity_hidden';
    }
}
