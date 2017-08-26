<?php

namespace Aero\Generator\Command;

use Aero\Generator\Steps;
use Notamedia\ConsoleJedi\Application\Command\BitrixCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Manage generation using console interface
 */
class GenerateCommand extends BitrixCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure(){
        $this->setName('aero:generate')
            ->setDescription('Start catalog generation process');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output){

        $steps = new Steps();

        $output->writeln('<info>Catalog generation process started</info>');
        $progress = new ProgressBar($output, $steps->getTotal());
        $progress->start();

        while($stepsCompleted = $steps->createNext())
            $progress->advance($stepsCompleted);

        $progress->finish();

        echo PHP_EOL;

        return 0;
    }
}