<?php

namespace NetBull\CoreBundle\Repository;

/**
 * Class RepositoryUtilsTrait
 * @package NetBull\CoreBundle\Repository
 */
trait RepositoryUtilsTrait
{
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
