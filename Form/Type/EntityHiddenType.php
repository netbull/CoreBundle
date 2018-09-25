<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class EntityHiddenType
 * @package NetBull\CoreBundle\Form\Type
 */
class EntityHiddenType extends AbstractType
{
    /**
     * @var DataTransformerInterface $transformer
     */
    private $transformer;

    /**
     * Constructor
     *
     * @param DataTransformerInterface $transformer
     */
    public function __construct(DataTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // attach the specified model transformer for this entity list field
        // this will convert data between object and string formats
        $builder->addModelTransformer($this->transformer);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return 'hidden';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'entity_hidden';
    }
}