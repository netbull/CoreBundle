<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * @var string
     */
    protected string $className;

    /**
     * EntityToPropertySimpleTransformer constructor.
     * @param EntityManagerInterface $em
     * @param $class
     */
    public function __construct(EntityManagerInterface $em, $class)
    {
        $this->em = $em;
        $this->className = $class;
    }

    /**
     * Transform entity to array
     * @param mixed $entity
     * @return mixed
     */
    public function transform(mixed $entity): mixed
    {
        if (null === $entity) {
            return $entity;
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        return $accessor->getValue($entity, 'id');
    }

    /**
     * Transform to single id value to an entity
     * @param mixed $value
     * @return mixed
     */
    public function reverseTransform(mixed $value): mixed
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
