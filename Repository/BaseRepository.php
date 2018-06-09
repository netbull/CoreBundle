<?php

namespace NetBull\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class BaseRepository
 * @package NetBull\CoreBundle\Repository
 */
class BaseRepository extends EntityRepository
{
    const CACHE_LIFETIME = 1800;
    const PHOTO_FIELDS = 'id,context,providerReference,providerName,name,width,height,main';

    ###################################################
    #                       Helpers                   #
    ###################################################

    /**
     * @param array $target
     * @param array $additions
     * @return array|null
     */
    protected function arrayCombine(array $target, array $additions)
    {
        $tmp = null;
        foreach ($additions as $addition) {
            if ($target['id'] === $addition['id']) {
                $tmp = array_merge($tmp ?? $target, $addition);
            }
        }

        return $tmp;
    }
}
