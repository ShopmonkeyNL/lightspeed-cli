<?php

namespace Davytimmers\LightspeedCli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Davytimmers\LightspeedCli\Services\InputOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class Pull extends Command
{
    /**
     * The name of the command (the part after "bin/demo").
     *
     * @var string
     */
    protected static $defaultName = 'pull';

    /**
     * The command description shown when running "php bin/demo list".
     *
     * @var string
     */
    protected static $defaultDescription = 'Pull current theme';

    /**
     * Execute the command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int 0 if everything went fine, or an exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        // $output->writeln('Bestand aanmaken in huidige map...');
        // $bestandsnaam = getcwd() . '/mijn_bestand.txt';
        // file_put_contents($bestandsnaam, 'Dit is de inhoud van mijn bestand.');
        // $output->writeln('Bestand aangemaakt: ' . $bestandsnaam);
        
        $io = new InputOutput($input, $output);
        $answer = $io->question('Kunt u antwoord geven?');
        $io->wrong('Er ging iets mis.');
        $io->right(sprintf('Het gegeven antwoord is: %s', $answer));
        
        return Command::SUCCESS;
    }
}