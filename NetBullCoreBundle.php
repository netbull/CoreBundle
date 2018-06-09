<?php

namespace NetBull\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
}
