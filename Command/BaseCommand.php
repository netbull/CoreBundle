<?php

namespace NetBull\CoreBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Class BaseCommand
 * @package NetBull\CoreBundle\Command
 */
abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * Debug switch
     * @var bool
     */
    protected $debug = false;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    public function getManager()
    {
        if (!$this->getContainer()->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/orm-pack".');
        }

        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Clear the Doctrine's cache
     */
    protected function optimize()
    {
        if ($this->getContainer()->has('doctrine')) {
            $this->getManager()->clear();
        }
    }

    /**
     * Output used for nice debug
     * @param $text
     */
    protected function output($text)
    {
        if (!$this->debug) {
            return;
        }

        $this->output->writeln($text);
    }
}
