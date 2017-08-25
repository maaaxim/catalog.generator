<?php

namespace Aero\Generator\Command;

use Notamedia\ConsoleJedi\Application\Command\BitrixCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

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

        // create a new progress bar (10 units)
        $progress = new ProgressBar($output, 10);

        // start and displays the progress bar
        $progress->start();

        $i = 0;
        while ($i++ < 10) {

            sleep(1);
            // ... do some work

            // advance the progress bar 1 unit
            $progress->advance();

            // you can also advance the progress bar by more than 1 unit
            // $progress->advance(3);
        }

        // ensure that the progress bar is at 100%
        $progress->finish();

        echo PHP_EOL;

        return 0;
    }
}