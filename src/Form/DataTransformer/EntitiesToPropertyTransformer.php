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
     * EntitiesToPropertyTransformer constructor.
     * @param EntityManagerInterface $em
     * @param $class
     * @param null $textProperty
     * @param string $primaryKey
     */
    public function __construct(EntityManagerInterface $em, $class, $textProperty = null, $primaryKey = 'id')
    {
        $this->em = $em;
        $this->className = $class;
        $this->textProperty = $textProperty;
        $this->primaryKey = $primaryKey;
    }

    /**
     * Transform initial entities to array
     * @param mixed $entities
     * @return array
     */
    public function transform($entities)
    {
        if (is_null($entities) || count($entities) === 0) {
            return [];
        }

        $data = [];
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($entities as $entity) {
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
     * @param array $values
     * @return ArrayCollection
     */
    public function reverseTransform($values)
    {
        if (!is_array($values) || count($values) === 0) {
            return new ArrayCollection();
        }

        // get multiple entities with one query
        $tags = new ArrayCollection();

        $entities = $this->em->createQueryBuilder()
            ->select('entity')
            ->from($this->className, 'entity')
            ->where('entity.'.$this->primaryKey.' IN (:ids)')
            ->setParameter('ids', $values)
            ->getQuery()
            ->getResult();

        return array_merge($entities, $tags->toArray());
    }
}
