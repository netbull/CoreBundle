<?php

namespace NetBull\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('netbull_core');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('form_types')
                    ->children()
                        ->arrayNode('ajax')
                            ->children()
                                ->scalarNode('minimum_input_length')->defaultValue(1)->end()
                                ->scalarNode('page_limit')->defaultValue(10)->end()
                                ->scalarNode('allow_clear')->defaultFalse()->end()
                                ->scalarNode('delay')->defaultValue(250)->end()
                                ->scalarNode('language')->defaultValue('en')->end()
                                ->scalarNode('cache')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
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
            ->end();


        $this->addFilesystemSection($rootNode);
        $this->addPaginatorSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addFilesystemSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('filesystem')
                    ->children()
                        ->arrayNode('s3')
                            ->children()
                                ->arrayNode('defaults')
                                    ->children()
                                        ->scalarNode('directory')->defaultValue('')->end()
                                        ->scalarNode('version')->defaultValue('latest')->end()
                                        ->scalarNode('region')->defaultValue('eu-central-1')->end()
                                        ->arrayNode('credentials')->isRequired()
                                            ->children()
                                                ->scalarNode('key')->isRequired()->end()
                                                ->scalarNode('secret')->isRequired()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()

                                ->arrayNode('options')
                                    ->children()
                                        ->scalarNode('bucket')->isRequired()->end()
                                        ->scalarNode('directory')->defaultValue('')->end()
                                        ->scalarNode('create')->defaultValue(false)->end()
                                        ->scalarNode('storage')
                                            ->defaultValue('standard')
                                            ->validate()
                                            ->ifNotInArray(['standard', 'reduced'])
                                            ->thenInvalid('Invalid storage type - "%s"')
                                            ->end()
                                        ->end()
                                        ->scalarNode('cache_control')->defaultValue('')->end()
                                        ->scalarNode('acl')
                                            ->defaultValue('public')
                                            ->validate()
                                            ->ifNotInArray(['private', 'public-read', 'open', 'auth_read', 'owner_read', 'owner_full_control'])
                                            ->thenInvalid('Invalid acl permission - "%s"')
                                            ->end()
                                        ->end()
                                        ->scalarNode('encryption')
                                            ->defaultValue('')
                                            ->validate()
                                            ->ifNotInArray(['aes256'])
                                            ->thenInvalid('Invalid encryption type - "%s"')
                                            ->end()
                                        ->end()
                                        ->arrayNode('meta')
                                            ->useAttributeAsKey('name')
                                            ->prototype('scalar')
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addPaginatorSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('paginator')
					->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('sortable')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('icons')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('none')->defaultValue('fa fa-sort')->end()
                                        ->scalarNode('asc')->defaultValue('fa fa-sort-up')->end()
                                        ->scalarNode('desc')->defaultValue('fa fa-sort-down')->end()
                                    ->end()
                                ->end()
                                ->scalarNode('active_class')->defaultValue('text-success')->end()
                                ->scalarNode('not_active_class')->defaultValue('text-primary')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
