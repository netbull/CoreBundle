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
     * @var bool
     */
    protected bool $debug = false;

    /**
     * @var OutputInterface
     */
    protected OutputInterface $output;

    /**
     * @var ObjectManager|null $em
     */
    protected ObjectManager|null $em = null;

    /**
     * @return ObjectManager
     */
    public function getManager(): ObjectManager
    {
        if (!$this->em) {
            throw new LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/orm-pack".');
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
     * @param $text
     */
    protected function output($text): void
    {
        if (!$this->debug) {
            return;
        }

        $this->output->writeln($text);
    }
}
