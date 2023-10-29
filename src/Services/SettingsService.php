<?php

namespace Davytimmers\LightspeedCli\Services;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Davytimmers\LightspeedCli\Services\InputOutput;

class SettingsService
{
    
    public $file;

    public function __construct() {
        $this->file = getcwd() . '/lightspeed-settings.json';
    }

    public function get($input, $output) {

        $file = $this->file;
        if (file_exists($file)) {
            $settings = file_get_contents($file); 
            $settings = json_decode($settings,true); 
            return $settings;
        } else {
            return $this->create($input, $output, $file);
        }

    }

    public function update($input, $output) {

        $io = new InputOutput($input, $output);
        $file = $this->file;
        $current_settings = file_get_contents($file); 
        $current_settings = json_decode($current_settings,true); 

        $csrf = $io->question('Enter current CSRF token');
        $backend_session_id = $io->question('Enter current backend session ID');

        $new_settings = $current_settings;
        $new_settings['csrf'] = $csrf;
        $new_settings['backend_session_id'] = $backend_session_id;

        file_put_contents($file, json_encode($new_settings, JSON_PRETTY_PRINT));

        return $new_settings;

    }

    private function create(InputInterface $input, OutputInterface $output, $file) {

        $io = new InputOutput($input, $output);
        $shop_url = $io->question('Enter shop URL');
        $theme_id = $io->question('Enter theme ID');
        $csrf = $io->question('Enter current CSRF token');
        $backend_session_id = $io->question('Enter current backend session ID');

        $settings =  [
            'theme_id' => $theme_id,
            'shop_url' => $shop_url,
            'csrf' => $csrf,
            'backend_session_id' => $backend_session_id     
        ];

        file_put_contents($file, json_encode($settings, JSON_PRETTY_PRINT));

        return $settings;

    }

}