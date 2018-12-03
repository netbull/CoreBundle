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
    protected $em;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $textProperty;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var array
     */
    protected $data;

    /**
     * EntityToPropertyTransformer constructor.
     * @param EntityManagerInterface $em
     * @param string $class
     * @param null $textProperty
     * @param string $primaryKey
     * @param array $data
     */
    public function __construct(EntityManagerInterface $em, string $class, $textProperty = null, $primaryKey = 'id', $data = [])
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
     * @return array
     */
    public function transform($entity)
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
     * @param string $value
     * @return mixed|null|object
     */
    public function reverseTransform($value)
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
