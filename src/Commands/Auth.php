<?php

namespace Davytimmers\LightspeedCli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Davytimmers\LightspeedCli\Services\SettingsService;
use Davytimmers\LightspeedCli\Services\InputOutput;

class Auth extends Command
{
    /**
     * The name of the command (the part after "bin/demo").
     *
     * @var string
     */
    protected static $defaultName = 'auth';

    /**
     * The command description shown when running "php bin/demo list".
     *
     * @var string
     */
    protected static $defaultDescription = 'Authenticate first time.';

    /**
     * Execute the command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int 0 if everything went fine, or an exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        
        $io = new InputOutput($input, $output);
        $settingsService = new SettingsService();
        $settings = $settingsService->get($input, $output);

        if (!$settingsService->authenticate($settings)) {
            $io->wrong("Something went wrong, try again. Check your settings.");
            $io->info(json_encode($settings, JSON_PRETTY_PRINT));
            $settingsService->create($input, $output);
            $this->execute($input, $output);
        } else {
            $io->right("Authentication successful for '". $settings['shop_url'] ."'.");
        }

        return Command::SUCCESS;
    }

}