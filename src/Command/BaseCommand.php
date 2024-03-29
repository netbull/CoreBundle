<?php

namespace NetBull\CoreBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
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
     * @var EntityManagerInterface $em
     */
    protected $em;

    /**
     * @return ObjectManager|object
     */
    public function getManager()
    {
        if (!$this->em) {
            throw new LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/orm-pack".');
        }

        return $this->em;
    }

    /**
     * Clear the Doctrine's cache
     */
    protected function optimize()
    {
        if ($this->em) {
            $this->em->clear();
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
