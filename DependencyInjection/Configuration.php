<?php

namespace Netbull\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('netbull_core');

        $this->addJSRouting($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    public function addJSRouting( ArrayNodeDefinition $node )
    {
        $node
            ->children()
                ->scalarNode('js_routes_path')
                    ->defaultNull()
                ->end()
                ->scalarNode('js_type')
                    ->defaultValue('js')
                    ->validate()
                        ->ifNotInArray(['js','es6'])
                        ->thenInvalid('The allowed options are js and es6')
                    ->end()
                ->end()
            ->end()
        ;
    }
}
