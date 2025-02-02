<?php

namespace NetBull\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class FormTwigTemplateCompilerPass implements CompilerPassInterface
{
    private string $telLayout = '@NetBullCore/Form/tel.html.twig';
    private string $telBootstrapLayout = '@NetBullCore/Form/tel_bootstrap.html.twig';

    /**
     * @param ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('twig.form.resources')) {
            return;
        }

        $parameter = $container->getParameter('twig.form.resources');

        if (in_array($this->telLayout, $parameter)) {
            return;
        }

        // Insert right after base template if it exists.
        if (($key = array_search('bootstrap_', $parameter)) !== false) {
            array_splice($parameter, ++$key, 0, array($this->telBootstrapLayout));
        } elseif (($key = array_search('form_div_layout.html.twig', $parameter)) !== false) {
            array_splice($parameter, ++$key, 0, array($this->telLayout));
        } else {
            // Put it in first position.
            array_unshift($parameter, array($this->telLayout));
        }

        $container->setParameter('twig.form.resources', $parameter);
    }
}
