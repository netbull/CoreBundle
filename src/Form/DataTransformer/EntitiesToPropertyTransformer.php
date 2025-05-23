<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Data transformer for multiple mode (i.e., multiple = true)
 * Class EntitiesToPropertyTransformer
 * @package NetBull\CoreBundle\Form\DataTransformer
 */
class EntitiesToPropertyTransformer implements DataTransformerInterface
{
    /**
     * @param EntityManagerInterface $em
     * @param string $className
     * @param string|null $textProperty
     * @param string $primaryKey
     */
    public function __construct(protected EntityManagerInterface $em, protected string $className, protected ?string $textProperty = null, protected string $primaryKey = 'id')
    {
    }

    /**
     * Transform initial entities to array
     * @param mixed $value
     * @return mixed
     */
    public function transform(mixed $value): mixed
    {
        if (is_null($value) || count($value) === 0) {
            return [];
        }

        $data = [];
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($value as $entity) {
            $text = is_null($this->textProperty)
                ? (string)$entity
                : $accessor->getValue($entity, $this->textProperty);
            $data[$accessor->getValue($entity, $this->primaryKey)] = [
                'text' => $text
            ];
        }

        return $data;
    }

    /**
     * Transform array to a collection of entities
     * @param mixed $value
     * @return mixed
     */
    public function reverseTransform(mixed $value): mixed
    {
        if (!is_array($value) || count($value) === 0) {
            return [];
        }

        // get multiple entities with one query
        $tags = new ArrayCollection();

        $entities = $this->em->createQueryBuilder()
            ->select('entity')
            ->from($this->className, 'entity')
            ->where('entity.'.$this->primaryKey.' IN (:ids)')
            ->setParameter('ids', $value)
            ->getQuery()
            ->getResult();

        return array_merge($entities, $tags->toArray());
    }
}
