<?php

namespace NetBull\CoreBundle\Command;

use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use NetBull\CoreBundle\Routing\Extractor;
use NetBull\CoreBundle\Routing\ExtractorInterface;

#[AsCommand(name: 'netbull:core:js-routing', description: 'Dumps exposed routes to the filesystem')]
class JSRoutingCommand extends Command
{
    /**
     * @var string
     */
    private string $targetPath;

    /**
     * @var Extractor|ExtractorInterface|null
     */
    private Extractor|null|ExtractorInterface $extractor;

    /**
     * @var bool
     */
    private bool $canExecute = true;

    /**
     * @var ParameterBagInterface|null
     */
    private ?ParameterBagInterface $parameterBag;

    /**
     * @param string|null $name
     * @param ParameterBagInterface|null $parameterBag
     * @param ExtractorInterface|null $extractor
     */
    public function __construct(string $name = null, ParameterBagInterface $parameterBag = null, ExtractorInterface $extractor = null)
    {
        parent::__construct($name);

        $this->parameterBag = $parameterBag;
        $this->extractor = $extractor;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption('target', null, InputOption::VALUE_OPTIONAL, 'Override the target directory to dump routes in.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        if (!$input->getOption('target') && !$this->parameterBag->get('netbull_core.js_routes_path')) {
            $output->writeln('<error>No exit file is specified!</error>');
            $output->writeln('Please specify it in netbull_core.js_routes_path');
            $this->canExecute = false;
        }

        $this->targetPath = $input->getOption('target') ?: $this->parameterBag->get('kernel.project_dir').'/'.$this->parameterBag->get('netbull_core.js_routes_path');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->canExecute) {
            return Command::SUCCESS;
        }

        $output->writeln('Dumping exposed routes.');
        $output->writeln('');
        $this->doDump($output);

        return Command::SUCCESS;
    }

    /**
     * Performs the routes dump.
     *
     * @param OutputInterface $output The command output
     */
    private function doDump(OutputInterface $output): void
    {
        if (!is_dir($dir = dirname($this->targetPath))) {
            $output->writeln('<info>[dir+]</info>  ' . $dir);
            if (false === @mkdir($dir, 0777, true)) {
                throw new RuntimeException('Unable to create directory ' . $dir);
            }
        }

        $output->writeln('<info>[file+]</info> ' . $this->targetPath);

        $templates = [
            'js' => "this.%s = route('%s');\n",
            'es6' => "\t\t\t'%s': '%s',\n"
        ];

        $type = $this->parameterBag->get('netbull_core.js_type');

        $routes = '';
        foreach ($this->extractor->getRoutes() as $name => $route) {
            preg_match_all("/{(.*?)}/i", $route->getPath(), $routeParams);

            if (0 < count($routeParams)) {
                $parameters = array_flip($routeParams[1]);
                $normalizedRoute = preg_replace_callback("/{(.*?)}/i", function($m) use($parameters) {
                    return ':' . ($parameters[$m[1]] + 1);
                }, $route->getPath());

                $routes .= sprintf($templates[$type], $name, $normalizedRoute);
            }
        }

        $source = file_get_contents(__DIR__ . '/../Resources/js/router.' . $type . '.js');
        $content = str_replace('//<ROUTES>', $routes, $source);

        if (false === @file_put_contents($this->targetPath, $content)) {
            throw new RuntimeException('Unable to write file ' . $this->targetPath);
        }
    }
}
