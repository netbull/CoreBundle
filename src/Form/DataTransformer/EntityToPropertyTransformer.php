<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Data transformer for single mode (i.e., multiple = false)
 * Class EntityToPropertyTransformer
 */
class EntityToPropertyTransformer implements DataTransformerInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected string $className,
        protected ?string $textProperty = null,
        protected string $primaryKey = 'id',
        protected array $data = [])
    {
    }

    /**
     * Transform entity to array
     */
    public function transform(mixed $value): array
    {
        $data = [];

        if (null === $value) {
            return [];
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $text = is_null($this->textProperty)
            ? (string) $value
            : $accessor->getValue($value, $this->textProperty);

        $attr = [];
        foreach ($this->data as $d) {
            $value = $accessor->getValue($value, $d);
            if ($value instanceof PersistentCollection || $value instanceof ArrayCollection) {
                $value = $value->first();
            }

            $attr[$d] = $value;
        }

        $data[$accessor->getValue($value, $this->primaryKey)] = [
            'text' => $text,
            'attr' => $attr,
        ];

        return $data;
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
        $entity = $repo->findOneBy([$this->primaryKey => $value]);

        if (!$entity) {
            return null;
        }

        return $entity;
    }
}
