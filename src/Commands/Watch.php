<?php

namespace Davytimmers\LightspeedCli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Watch extends Command
{
    /**
     * The name of the command (the part after "bin/demo").
     *
     * @var string
     */
    protected static $defaultName = 'watch';

    /**
     * The command description shown when running "php bin/demo list".
     *
     * @var string
     */
    protected static $defaultDescription = 'Watch files and push them to shop.';

    /**
     * Execute the command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int 0 if everything went fine, or an exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $command = 'gulp watch';
        $descriptorspec = array(
            0 => array('pipe', 'r'), // Standaard invoer
            1 => array('pipe', 'w'), // Standaard uitvoer
            2 => array('pipe', 'w')  // Standaard fout
        );

        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {
            stream_set_blocking($pipes[1], 0); // Zorg ervoor dat de uitvoer niet gebufferd wordt

            while (!feof($pipes[1])) {
                $output = fgets($pipes[1]);
                echo $output;
                ob_flush();
                flush();
            }

            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }

        // $output = exec('gulp watch');
        // echo $output;
        return Command::SUCCESS;
    }

}