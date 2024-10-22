<?php

namespace NetBull\CoreBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class NetBullCoreExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('netbull_core.js_routes_path', $config['js_routes_path']);
        $container->setParameter('netbull_core.js_type', $config['js_type']);

        // Make manifest parameter optional
        if (!$container->hasParameter('manifest')) {
            $container->setParameter('manifest', []);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        // set parameters with the default settings so they'll be available in the service definition yml
        $varNames = ['minimum_input_length', 'page_limit', 'allow_clear', 'delay', 'language', 'cache'];
        if (!empty($config['form_types']) && !empty($config['form_types']['ajax'])) {
            foreach($varNames as $varName) {
                $container->setParameter('netbull_core.form_types.ajax.' . $varName, $config['form_types']['ajax'][$varName]);
            }
        }

        foreach($config['paginator'] as $key => $value) {
            $this->setParameters($container, $key, $value, '.paginator');
        }

        $loader->load('forms.yaml');
    }

    /**
     * @param ContainerBuilder $container
     * @param string $key
     * @param array|string $value
     * @param string $suffix
     * @return void
     */
    private function setParameters(ContainerBuilder $container, string $key, array|string $value, string $suffix = ''): void
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $this->setParameters($container, $k, $v, $suffix.'.'.$key);
            }
        } else {
            $container->setParameter('netbull_core'.$suffix.'.'.$key, $value);
        }
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'netbull_core';
    }
}
