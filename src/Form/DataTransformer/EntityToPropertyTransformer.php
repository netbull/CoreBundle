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
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * @var string
     */
    protected string $className;

    /**
     * @var string|null
     */
    protected ?string $textProperty;

    /**
     * @var string
     */
    protected string $primaryKey;

    /**
     * @var array
     */
    protected array $data;

    /**
     * @param EntityManagerInterface $em
     * @param string $class
     * @param string|null $textProperty
     * @param string $primaryKey
     * @param array $data
     */
    public function __construct(EntityManagerInterface $em, string $class, string $textProperty = null, string $primaryKey = 'id', array $data = [])
    {
        $this->em = $em;
        $this->className = $class;
        $this->textProperty = $textProperty;
        $this->primaryKey = $primaryKey;
        $this->data = $data;
    }

    /**
     * Transform entity to array
     * @param mixed $entity
     * @return mixed
     */
    public function transform(mixed $entity): mixed
    {
        $data = [];

        if (null === $entity) {
            return $data;
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $text = is_null($this->textProperty)
            ? (string)$entity
            : $accessor->getValue($entity, $this->textProperty);

        $attr = [];
        foreach ( $this->data as $d ) {
            $value = $accessor->getValue($entity, $d);
            if ( $value instanceof PersistentCollection || $value instanceof ArrayCollection ) {
                $value = $value->first();
            }

            $attr[$d] = $value;
        }

        $data[$accessor->getValue($entity, $this->primaryKey)] = [
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
