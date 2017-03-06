<?php

namespace Netbull\CoreBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Netbull\CoreBundle\Routing\Extractor;

/**
 * Class JSRoutingCommand
 * @package Netbull\CoreBundle\Command
 */
class JSRoutingCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    private $targetPath;

    /**
     * @var Extractor
     */
    private $extractor;

    /**
     * @var bool
     */
    private $canExecute = true;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('netbull:core:js-routing')
            ->setDescription('Dumps exposed routes to the filesystem')
            ->addOption(
                'target',
                null,
                InputOption::VALUE_OPTIONAL,
                'Override the target directory to dump routes in.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $container = $this->getContainer();

        if ( !$input->getOption('target') && !$container->getParameter('netbull_core.js_routes_path') ) {
            $output->writeln('<error>No exit file is specified!</error>');
            $output->writeln('Please specify it in netbull_core.js_routes_path');
            $this->canExecute = false;
        }

        $this->targetPath   = $input->getOption('target') ?: sprintf('%s/../%s', $container->getParameter('kernel.root_dir'), $container->getParameter('netbull_core.js_routes_path'));
        $this->extractor    = $this->getContainer()->get('netbull_core.js_routing');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ( !$this->canExecute ) {
            return;
        }

        $output->writeln('Dumping exposed routes.');
        $output->writeln('');
        $this->doDump($output);
    }

    /**
     * Performs the routes dump.
     *
     * @param OutputInterface $output The command output
     */
    private function doDump( OutputInterface $output )
    {
        if ( !is_dir($dir = dirname($this->targetPath)) ) {
            $output->writeln('<info>[dir+]</info>  ' . $dir);
            if ( false === @mkdir($dir, 0777, true) ) {
                throw new \RuntimeException('Unable to create directory ' . $dir);
            }
        }

        $output->writeln('<info>[file+]</info> ' . $this->targetPath);

        $routes = '';
        foreach ( $this->extractor->getRoutes() as $name => $route ) {
            preg_match_all("/{(.*?)}/i", $route->getPath(), $routeParams);

            if ( count($routeParams) > 0 ) {
                $parameters = array_flip($routeParams[1]);
                $normalizedRoute = preg_replace_callback("/{(.*?)}/i", function( $m ) use( $parameters ){
                    return ':' . ($parameters[$m[1]] + 1);
                }, $route->getPath());

                $routes .= sprintf("this.%s = route('%s');\n", $name, $normalizedRoute);
            }
        }

        $source = file_get_contents($this->getContainer()->get('kernel')->locateResource('@NetbullCoreBundle/Resources/js/Router.js'));
        $content = str_replace('//<ROUTES>', $routes, $source);

        if ( false === @file_put_contents($this->targetPath, $content) ) {
            throw new \RuntimeException('Unable to write file ' . $this->targetPath);
        }
    }
}
