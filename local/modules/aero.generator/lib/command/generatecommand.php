<?php

namespace Aero\Generator\Command;

use Notamedia\ConsoleJedi\Application\Command\BitrixCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 26.08.2017
 * Time: 0:04
 */
class GenerateCommand extends BitrixCommand
{
    protected function configure()
    {
        $this->setName('aero:generate')
            ->setDescription('Start catalog generation process');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Yohoho!</info>');
    }
}