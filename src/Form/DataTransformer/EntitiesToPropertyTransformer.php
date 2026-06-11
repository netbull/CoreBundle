<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Data transformer for multiple mode (i.e., multiple = true)
 * Class EntitiesToPropertyTransformer
 */
class EntitiesToPropertyTransformer implements DataTransformerInterface
{
    public function __construct(protected EntityManagerInterface $em, protected string $className, protected ?string $textProperty = null, protected string $primaryKey = 'id')
    {
    }

    /**
     * Transform initial entities to array
     */
    public function transform(mixed $value): mixed
    {
        if (is_null($value) || 0 === count($value)) {
            return [];
        }

        $data = [];
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($value as $entity) {
            $text = is_null($this->textProperty)
                ? (string) $entity
                : $accessor->getValue($entity, $this->textProperty);
            $data[$accessor->getValue($entity, $this->primaryKey)] = [
                'text' => $text,
            ];
        }

        return $data;
    }

    /**
     * Transform array to a collection of entities
     */
    public function reverseTransform(mixed $value): mixed
    {
        if (!is_array($value) || 0 === count($value)) {
            return [];
        }

        // get multiple entities with one query
        $tags = new ArrayCollection();

        $entities = $this->em->createQueryBuilder()
            ->select('entity')
            ->from($this->className, 'entity')
            ->where('entity.' . $this->primaryKey . ' IN (:ids)')
            ->setParameter('ids', $value)
            ->getQuery()
            ->getResult();

        return array_merge($entities, $tags->toArray());
    }
}
