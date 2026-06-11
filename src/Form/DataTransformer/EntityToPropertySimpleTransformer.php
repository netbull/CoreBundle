<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Data transformer for single entity
 * Class EntityToPropertySimpleTransformer
 */
class EntityToPropertySimpleTransformer implements DataTransformerInterface
{
    public function __construct(protected EntityManagerInterface $em, protected string $className)
    {
    }

    /**
     * Transform entity to array
     */
    public function transform(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        return $accessor->getValue($value, 'id');
    }

    /**
     * Transform to single id value to an entity
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
