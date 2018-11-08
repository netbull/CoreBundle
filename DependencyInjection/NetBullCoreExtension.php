<?php

namespace NetBull\CoreBundle\DependencyInjection;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class NetBullCoreExtension
 * @package NetBull\CoreBundle\DependencyInjection
 */
class NetBullCoreExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
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

        $loader->load('translations.yaml');

        $loader->load('form.yaml');
        // set parameters with the default settings so they'll be available in the service definition yml
        $varNames = ['minimum_input_length', 'page_limit', 'allow_clear', 'delay', 'language', 'cache'];
        if (!empty($config['form_types']) && !empty($config['form_types']['ajax'])) {
            foreach($varNames as $varName) {
                $container->setParameter('netbull_core.form_types.ajax.' . $varName, $config['form_types']['ajax'][$varName]);
            }
        } else {
            $container->removeDefinition('netbull_core.form.type.dynamic');
            $container->removeDefinition('netbull_core.form.type.ajax');
            $container->removeDefinition('netbull_core.form.type.select2');
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param $name
     * @param $config
     */
    public function bindParameters(ContainerBuilder $container, $name, $config)
    {
        if (is_array($config) && empty($config[0])) {
            foreach ($config as $key => $value) {
                if ('locale_map' === $key) {
                    //need a assoc array here
                    $container->setParameter($name . '.' . $key, $value);
                } else {
                    $this->bindParameters($container, $name . '.' . $key, $value);
                }
            }
        } else {
            $container->setParameter($name, $config);
        }
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'netbull_core';
    }
}
