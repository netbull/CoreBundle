<?php

namespace NetBull\CoreBundle;

use Exception;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class NetBullCoreBundle extends AbstractBundle
{
    protected string $extensionAlias = 'netbull_core';

    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition->rootNode();

        $rootNode
            ->children()
                ->arrayNode('form_types')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('ajax')
                            ->addDefaultsIfNotSet()
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
                        ->ifNotInArray(['js', 'es6'])
                        ->thenInvalid('The allowed options are js and es6')
                    ->end()
                ->end()
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

    /**
     * @throws Exception
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
        $container->import('../config/forms.yaml');

        $builder->setParameter('netbull_core.js_routes_path', $config['js_routes_path']);
        $builder->setParameter('netbull_core.js_type', $config['js_type']);

        // Make manifest parameter optional
        if (!$builder->hasParameter('manifest')) {
            $builder->setParameter('manifest', []);
        }

        // expose the resolved defaults as parameters so they are available in the service definitions
        foreach ($config['form_types']['ajax'] as $varName => $value) {
            $builder->setParameter('netbull_core.form_types.ajax.' . $varName, $value);
        }

        foreach ($config['paginator'] as $key => $value) {
            $this->setParameters($builder, $key, $value, '.paginator');
        }
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    private function setParameters(ContainerBuilder $builder, string $key, array|string $value, string $suffix = ''): void
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $this->setParameters($builder, $k, $v, $suffix . '.' . $key);
            }
        } else {
            $builder->setParameter('netbull_core' . $suffix . '.' . $key, $value);
        }
    }
}
