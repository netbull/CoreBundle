<?php

namespace NetBull\CoreBundle\ORM\Subscribers\Sluggable;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

/**
 * Class SluggableSubscriber
 * @package NetBull\CoreBundle\ORM\Subscribers\Sluggable
 */
class SluggableSubscriber extends AbstractSubscriber
{
    private $sluggableTrait;

    /**
     * SluggableSubscriber construector.
     * @param ClassAnalyzer $classAnalyzer
     * @param $isRecursive
     * @param $sluggableTrait
     */
    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, $sluggableTrait)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->sluggableTrait = $sluggableTrait;
    }

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isSluggable($classMetadata)) {
            if (!$classMetadata->hasField('slug')) {
                $classMetadata->mapField([
                    'fieldName' => 'slug',
                    'type'      => 'string',
                    'nullable'  => true
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isSluggable($classMetadata)) {
            $entity->generateSlug();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isSluggable($classMetadata)) {
            $entity->generateSlug();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $uow->computeChangeSets();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::prePersist,
            Events::preUpdate,
            Events::onFlush
        ];
    }

    /**
     * Checks if entity is sluggable
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return boolean
     */
    private function isSluggable(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->sluggableTrait,
            $this->isRecursive
        );
    }
}
