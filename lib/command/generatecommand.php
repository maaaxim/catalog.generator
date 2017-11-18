<?php

namespace Catalog\Generator\Command;

use Catalog\Generator\Steps;
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
        $this->setName('catalog.generator:start')
            ->setDescription('Start catalog generation process');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output){
        $output->writeln('<info>Catalog generation process started</info>');
        $steps = new Steps();
        if($steps->getCount() <= 0)
            $steps->firstStep();
        $progress = new ProgressBar($output, $steps->getCount());
        $progress->setRedrawFrequency(1);
        $progress->start();
        while($stepsCompleted = $steps->createNext()){
            $progress->advance();
        }
        $progress->finish();
        echo PHP_EOL;
        return 0;
    }
}