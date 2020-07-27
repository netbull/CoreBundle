<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CollectionType as BaseCollectionType;

/**
 * Class AutoCollectionType
 * @package NetBull\CoreBundle\Form\Type
 */
class AutoCollectionType extends BaseCollectionType
{
    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'auto_collection';
    }
}
