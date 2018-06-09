<?php

namespace NetBull\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Class Configuration
 * @package NetBull\CoreBundle\DependencyInjection
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
        $this->addLocaleSection($rootNode);

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
            ->end()
        ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addLocaleSection( ArrayNodeDefinition $node )
    {
        $validStatusCodes = [300, 301, 302, 303, 307];

        $node
            ->children()
                ->arrayNode('locale')
                    ->children()
                        ->booleanNode('disable_vary_header')->defaultFalse()->end()
                        ->scalarNode('guessing_excluded_pattern')
                            ->defaultNull()
                        ->end()
                        ->arrayNode('allowed_locales')
                            ->defaultValue(['en'])
                            ->requiresAtLeastOneElement()
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('locale_map')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('guessing_order')
                                ->beforeNormalization()
                                    ->ifString()
                                        ->then(function ($v) { return [$v]; })
                                ->end()
                                ->defaultValue(['cookie'])
                                ->requiresAtLeastOneElement()
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('cookie')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('set_on_change')->defaultTrue()->end()
                                ->scalarNode('class')->defaultValue('NetBull\CoreBundle\Locale\Cookie\LocaleCookie')->end()
                                ->scalarNode('name')->defaultValue('netbull_locale')->end()
                                ->scalarNode('ttl')->defaultValue('86400')->end()
                                ->scalarNode('path')->defaultValue('/')->end()
                                ->scalarNode('domain')->defaultValue(null)->end()
                                ->scalarNode('secure')->defaultFalse()->end()
                                ->scalarNode('httpOnly')->defaultTrue()->end()
                             ->end()
                        ->end()
                        ->arrayNode('session')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('variable')->defaultValue('netbull_locale')->end()
                             ->end()
                        ->end()
                        ->arrayNode('switcher')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('template')->defaultValue('links')->end()
                                ->booleanNode('show_current_locale')->defaultFalse()->end()
                                ->scalarNode('redirect_to_route')->defaultNull()->end()
                                ->scalarNode('redirect_statuscode')->defaultValue('302')->end()
                                ->booleanNode('use_controller')->defaultFalse()->end()
                                ->booleanNode('use_referrer')->defaultTrue()->end()
                            ->end()
                            ->validate()
                                ->ifTrue(function($v) { return is_null($v['redirect_to_route']);})
                                    ->thenInvalid('You need to specify a default fallback route for the use_controller configuration')
                                ->ifTrue(function($v) use ($validStatusCodes) { return !in_array(intval($v['redirect_statuscode']), $validStatusCodes);})
                                    ->thenInvalid(sprintf("Invalid HTTP statuscode. Available statuscodes for redirection are:\n\n%s \n\nSee reference for HTTP status codes", implode(", ", $validStatusCodes)))
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
