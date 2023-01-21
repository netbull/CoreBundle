<?php

namespace NetBull\CoreBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use NetBull\CoreBundle\DependencyInjection\NetBullCoreExtension;

class NetBullCoreBundle extends Bundle
{
    /**
     * @return NetBullCoreExtension
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new NetBullCoreExtension();
    }
}
