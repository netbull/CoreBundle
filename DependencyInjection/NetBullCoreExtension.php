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

        $useLocale = isset($configs[0]['locale']);

        if ($useLocale) {
            $this->bindParameters($container, 'netbull_locale', $config['locale']);

            // Fallback for missing intl extension
            $intlExtensionInstalled = extension_loaded('intl');
            $container->setParameter('netbull_core.intl_extension_installed', $intlExtensionInstalled);
            $iso3166        = [];
            $iso639one      = [];
            $iso639two      = [];
            $localeScript   = [];
            if (!$intlExtensionInstalled) {
                $yamlParser = new Parser();
                $file = new FileLocator(__DIR__ . '/../Resources/config/locale');
                $iso3166 = $yamlParser->parse(file_get_contents($file->locate('iso3166-1-alpha-2.yaml')));
                $iso639one = $yamlParser->parse(file_get_contents($file->locate('iso639-1.yaml')));
                $iso639two = $yamlParser->parse(file_get_contents($file->locate('iso639-2.yaml')));
                $localeScript = $yamlParser->parse(file_get_contents($file->locate('locale_script.yaml')));
            }

            $container->setParameter('netbull_core.intl_extension_fallback.iso3166', $iso3166);
            $mergedValues = array_merge($iso639one, $iso639two);
            $container->setParameter('netbull_core.intl_extension_fallback.iso639', $mergedValues);
            $container->setParameter('netbull_core.intl_extension_fallback.script', $localeScript);
        }

        // set parameter for the assets CDN
        if(isset($config['assets_cdn']) && !empty($config['assets_cdn'])){
            $container->setParameter('netbull_core.assets.cdn', $config['assets_cdn']);
        }

        // set parameters with the default settings so they'll be available in the service definition yml
        $varNames = ['minimum_input_length', 'page_limit', 'allow_clear', 'delay', 'language', 'cache'];
        foreach($varNames as $varName) {
            $container->setParameter('netbull_core.form_types.ajax.' . $varName, $config['form_types']['ajax'][$varName]);
        }

        $container->setParameter('netbull_core.js_routes_path', $config['js_routes_path']);
        $container->setParameter('netbull_core.js_type', $config['js_type']);

        // Make manifest parameter optional
        if (!$container->hasParameter('manifest')) {
            $container->setParameter('manifest', []);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        if ($useLocale) {
            $loader->load('locale.yaml');
        }
        $loader->load('form.yaml');
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
}
