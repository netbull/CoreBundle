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
     * @param EntityManagerInterface $em
     * @param string $class
     * @param string|null $textProperty
     * @param string $primaryKey
     */
    public function __construct(EntityManagerInterface $em, string $class, string $textProperty = null, string $primaryKey = 'id')
    {
        $this->em = $em;
        $this->className = $class;
        $this->textProperty = $textProperty;
        $this->primaryKey = $primaryKey;
    }

    /**
     * Transform initial entities to array
     * @param mixed $entities
     * @return mixed
     */
    public function transform(mixed $entities): mixed
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
     * @param mixed $values
     * @return mixed
     */
    public function reverseTransform(mixed $values): mixed
    {
        if (!is_array($values) || count($values) === 0) {
            return [];
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
