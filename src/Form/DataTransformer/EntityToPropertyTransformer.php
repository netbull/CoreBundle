<?php

namespace NetBull\CoreBundle\Form\DataTransformer;

use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Data transformer for single mode (i.e., multiple = false)
 * Class EntityToPropertyTransformer
 * @package NetBull\CoreBundle\Form\DataTransformer
 */
class EntityToPropertyTransformer implements DataTransformerInterface
{
    /**
     * @param EntityManagerInterface $em
     * @param string $className
     * @param string|null $textProperty
     * @param string $primaryKey
     * @param array $data
     */
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
     * @param mixed $value
     * @return array
     */
    public function transform(mixed $value): array
    {
        $data = [];

        if (null === $value) {
            return [];
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $text = is_null($this->textProperty)
            ? (string)$value
            : $accessor->getValue($value, $this->textProperty);

        $attr = [];
        foreach ( $this->data as $d ) {
            $value = $accessor->getValue($value, $d);
            if ( $value instanceof PersistentCollection || $value instanceof ArrayCollection ) {
                $value = $value->first();
            }

            $attr[$d] = $value;
        }

        $data[$accessor->getValue($value, $this->primaryKey)] = [
            'text' => $text,
            'attr' => $attr
        ];
        return $data;
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
        $entity = $repo->findOneBy([$this->primaryKey => $value]);

        if (!$entity) {
            return null;
        }

        return $entity;
    }
}
