<?php

namespace NetBull\CoreBundle\Tests\DependencyInjection;

use NetBull\CoreBundle\NetBullCoreBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NetBullCoreExtensionTest extends TestCase
{
    public function testExtensionAliasAndPath(): void
    {
        $bundle = new NetBullCoreBundle();

        self::assertSame('netbull_core', $bundle->getContainerExtension()->getAlias());
        self::assertSame(\dirname(__DIR__, 2), $bundle->getPath());
        self::assertDirectoryExists($bundle->getPath() . '/config');
        self::assertDirectoryExists($bundle->getPath() . '/templates');
    }

    public function testLoadWithEmptyConfigDefinesAjaxAndPaginatorParameters(): void
    {
        $container = $this->loadContainer([]);

        self::assertSame(1, $container->getParameter('netbull_core.form_types.ajax.minimum_input_length'));
        self::assertSame(10, $container->getParameter('netbull_core.form_types.ajax.page_limit'));
        self::assertFalse($container->getParameter('netbull_core.form_types.ajax.allow_clear'));
        self::assertSame(250, $container->getParameter('netbull_core.form_types.ajax.delay'));
        self::assertSame('en', $container->getParameter('netbull_core.form_types.ajax.language'));
        self::assertTrue($container->getParameter('netbull_core.form_types.ajax.cache'));

        self::assertSame('text-success', $container->getParameter('netbull_core.paginator.sortable.active_class'));
        self::assertSame('fa fa-sort-up', $container->getParameter('netbull_core.paginator.sortable.icons.asc'));

        self::assertNull($container->getParameter('netbull_core.js_routes_path'));
        self::assertSame('js', $container->getParameter('netbull_core.js_type'));
    }

    public function testLoadRegistersServices(): void
    {
        $container = $this->loadContainer([]);

        self::assertTrue($container->hasDefinition('NetBull\CoreBundle\Paginator\Paginator'));
        self::assertTrue($container->hasDefinition('NetBull\CoreBundle\Routing\Extractor'));
        self::assertTrue($container->hasAlias('NetBull\CoreBundle\Routing\ExtractorInterface'));
        self::assertTrue($container->hasDefinition('NetBull\CoreBundle\Form\Type\AjaxType'));
        self::assertTrue($container->hasDefinition('NetBull\CoreBundle\Form\Type\Select2Type'));

        $command = $container->getDefinition('NetBull\CoreBundle\Command\JSRoutingCommand');
        self::assertTrue($command->hasTag('console.command'));

        $util = $container->getDefinition('libphonenumber.phone_number_util');
        self::assertNotNull($util->getFactory(), 'PhoneNumberUtil has a protected constructor and must be built via factory');
    }

    public function testLoadOverridesDefaultsFromConfig(): void
    {
        $container = $this->loadContainer([
            'js_routes_path' => 'assets/js/router.js',
            'js_type' => 'es6',
            'form_types' => ['ajax' => ['page_limit' => 25]],
        ]);

        self::assertSame('assets/js/router.js', $container->getParameter('netbull_core.js_routes_path'));
        self::assertSame('es6', $container->getParameter('netbull_core.js_type'));
        self::assertSame(25, $container->getParameter('netbull_core.form_types.ajax.page_limit'));
        // untouched keys keep their defaults
        self::assertSame(1, $container->getParameter('netbull_core.form_types.ajax.minimum_input_length'));
    }

    private function loadContainer(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');
        $container->setParameter('kernel.build_dir', sys_get_temp_dir());
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir());
        $container->setParameter('kernel.bundles', []);

        (new NetBullCoreBundle())->getContainerExtension()->load([$config], $container);

        return $container;
    }
}
