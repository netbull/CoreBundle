<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Data transformer for single entity
 * Class EntityToPropertySimpleTransformer
 * @package NetBull\CoreBundle\Form\DataTransformer
 */
class EntityToPropertySimpleTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $className;

    /**
     * EntityToPropertyTransformer constructor.
     * @param EntityManager     $em
     * @param                   $class
     */
    public function __construct( EntityManager $em, $class )
    {
        $this->em               = $em;
        $this->className        = $class;
    }

    /**
     * Transform entity to array
     * @param mixed $entity
     * @return array
     */
    public function transform( $entity )
    {
        if (null === $entity) {
            return $entity;
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        return $accessor->getValue($entity, 'id');
    }

    /**
     * Transform to single id value to an entity
     * @param string $value
     * @return mixed|null|object
     */
    public function reverseTransform( $value )
    {
        if (null === $value) {
            return null;
        }

        $repo = $this->em->getRepository($this->className);
        $entity = $repo->find($value);

        if (!$entity) {
            return null;
        }

        return $entity;
    }
}
