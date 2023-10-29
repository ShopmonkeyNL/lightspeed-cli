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
            return $this->create($input, $output);
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

    public function create($input, $output) {

        $io = new InputOutput($input, $output);
        $file = $this->file;
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

    public function authenticate($settings) {
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $settings['shop_url'].'admin/themes/'.$settings['theme_id'].'/templates.json',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json, text/plain, */*',
            'Content-Type: application/json;charset=UTF-8',
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Site: same-origin',
            'x-csrf-token: '.$settings['csrf'],
            'Cookie: shared_session_id='.$settings['backend_session_id'].'; backend_session_id='.$settings['backend_session_id'].'; request_method=GET'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // $response = json_decode($response, true);

        if (!$response) {
            return false;
        } else {
            $response = json_decode($response, true);
            if (isset($response['error'])) {
                return false;
            } else {
                return true;
            }
        }

    }

}