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
     * @param EntityManagerInterface $em
     * @param string $className
     */
    public function __construct(protected EntityManagerInterface $em, protected string $className)
    {
    }

    /**
     * Transform entity to array
     * @param mixed $value
     * @return mixed
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
