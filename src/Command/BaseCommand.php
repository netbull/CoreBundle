<?php

namespace NetBull\CoreBundle\Command;

use Doctrine\Persistence\ObjectManager;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{
    /**
     * Debug switch
     */
    protected bool $debug = false;

    protected OutputInterface $output;

    protected ?ObjectManager $em = null;

    public function getManager(): ObjectManager
    {
        if (!$this->em) {
            throw new LogicException('The DoctrineBundle is not registered in your application. Try running "composer require doctrine/orm doctrine/doctrine-bundle".');
        }

        return $this->em;
    }

    /**
     * Clear the Doctrine's cache
     */
    protected function optimize(): void
    {
        if ($this->em) {
            $this->em->clear();
        }
    }

    /**
     * Output used for nice debug
     */
    protected function output($text): void
    {
        if (!$this->debug) {
            return;
        }

        $this->output->writeln($text);
    }
}
