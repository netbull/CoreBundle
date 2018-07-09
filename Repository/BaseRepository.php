<?php

namespace NetBull\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class BaseRepository
 * @package NetBull\CoreBundle\Repository
 */
class BaseRepository extends EntityRepository
{
    ###################################################
    #                       Helpers                   #
    ###################################################

    /**
     * @param array $target
     * @param array $additions
     * @return array|null
     */
    public static function arrayCombine(array $target, array $additions)
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
