<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CollectionType as BaseCollectionType;

class AutoCollectionType extends BaseCollectionType
{
    public function getBlockPrefix(): string
    {
        return 'auto_collection';
    }
}
