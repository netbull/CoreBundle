<?php

namespace NetBull\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

use NetBull\CoreBundle\DependencyInjection\NetBullCoreExtension;

/**
 * Class NetBullCoreBundle
 * @package NetBull\CoreBundle
 */
class NetBullCoreBundle extends Bundle
{
    /**
     * @return NetBullCoreExtension|null|\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new NetBullCoreExtension();
    }
}
