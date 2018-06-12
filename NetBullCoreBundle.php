<?php

namespace NetBull\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use NetBull\CoreBundle\DependencyInjection\NetBullCoreExtension;
use NetBull\CoreBundle\DependencyInjection\Compiler\GuesserCompilerPass;

/**
 * Class NetBullCoreBundle
 * @package NetBull\CoreBundle
 */
class NetBullCoreBundle extends Bundle
{
    /**
     * Add CompilerPass
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GuesserCompilerPass);
    }

    /**
     * @return NetBullCoreExtension|null|\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new NetBullCoreExtension();
    }
}
