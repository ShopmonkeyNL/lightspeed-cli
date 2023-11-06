<?php

namespace Davytimmers\LightspeedCli\Commands;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Davytimmers\LightspeedCli\Services\InputOutput;
use Davytimmers\LightspeedCli\Services\SettingsService;

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

        $io              = new InputOutput($input, $output);
        $settingsService = new SettingsService();
        $settings        = $settingsService->get($input, $output);

        $this->installWatcherFiles();
        $this->addRecommendedExtensions();
        $this->createPrettierConfig();

        $availableToPull = $this->availableToPull($settings);
        if (!$availableToPull) {
            $io->wrong('Seems like you have to log in again.');
            $settings = $settingsService->update($input, $output);
            $this->execute($input, $output);
            return false;
        }

        $this->deleteThemeFilesLocal();

        $templates = $this->getTemplates($settings);
        foreach ($templates as $template) {
            $this->saveTemplate($template);
        }
        $assets = $this->getAssets($settings);
        foreach ($assets as $assets) {
            $this->saveAsset($assets);
        }

        $io->right("Theme pulled successfuly from '" . $settings['shop_url'] . "'.");

        // TODO: pull settings
        // TODO: pull settings data

        return Command::SUCCESS;
    }

    private function installWatcherFiles()
    {

        $oldDir        = __DIR__ . '/../WatcherFiles/';
        $oldWatcher    = $oldDir . 'filewatcher.php';
        $newWatcher    = getcwd() . '/.functions/filewatcher.php';
        $oldGulpFile   = $oldDir . 'gulpfile.js';
        $newGulpFile   = getcwd() . '/gulpfile.js';
        $oldGulpConfig = $oldDir . 'gulp.config.js';
        $newGulpConfig = getcwd() . '/gulp.config.js';

        $this->installNodeDependencies();
        $this->createFilesAndFolders();

        if (!is_dir(getcwd() . '/.functions')) {
            mkdir((getcwd() . '/.functions'), 0755, true);
        }

        copy($oldWatcher, $newWatcher);
        copy($oldGulpFile, $newGulpFile);
        copy($oldGulpConfig, $newGulpConfig);
    }

    private function getTemplates($settings)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $settings['shop_url'] . 'admin/themes/' . $settings['theme_id'] . '/templates.json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => array(
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/json;charset=UTF-8',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'x-csrf-token: ' . $settings['csrf'],
                'Cookie: shared_session_id=' . $settings['backend_session_id'] . '; backend_session_id=' . $settings['backend_session_id'] . '; request_method=GET'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response, true);

        return $response['theme_templates'];
    }

    private function getAssets($settings)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $settings['shop_url'] . 'admin/themes/' . $settings['theme_id'] . '/assets.json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => array(
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/json;charset=UTF-8',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'x-csrf-token: ' . $settings['csrf'],
                'Cookie: shared_session_id=' . $settings['backend_session_id'] . '; backend_session_id=' . $settings['backend_session_id'] . '; request_method=GET'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response, true);

        return $response['theme_assets'];
    }

    private function saveTemplate($template)
    {
        $base  = getcwd() . '/';
        $parts = pathinfo($template['key']);
        if (!is_dir($base . $parts['dirname'])) {
            mkdir($base . $parts['dirname'], 0755, true);
        }
        $fullPath = $base . $parts['dirname'] . '/' . $parts['basename'];
        file_put_contents($fullPath, $template['content']);
        echo "Loaded: " . $template['key'] . "\n";
    }

    private function saveAsset($asset)
    {
        // if (!in_array($asset['extension'], ['png', 'jpg', 'jpeg', 'woff', 'woff2', 'ttf'])) {
        $base  = getcwd() . '/';
        $parts = pathinfo($asset['key']);
        if (!is_dir($base . $parts['dirname'])) {
            mkdir($base . $parts['dirname'], 0755, true);
        }
        $fullPath = $base . $parts['dirname'] . '/' . $parts['basename'];
        file_put_contents($fullPath, file_get_contents($asset['src']));
        echo "Loaded: " . $asset['key'] . "\n";
        // }
    }

    private function availableToPull($settings)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $settings['shop_url'] . 'admin/themes/' . $settings['theme_id'] . '/templates.json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => array(
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/json;charset=UTF-8',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'x-csrf-token: ' . $settings['csrf'],
                'Cookie: shared_session_id=' . $settings['backend_session_id'] . '; backend_session_id=' . $settings['backend_session_id'] . '; request_method=GET'
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

    private function deleteThemeFilesLocal()
    {

        $dirs = ['/assets', '/layouts', '/pages', '/snippets'];

        foreach ($dirs as $dir) {
            $dir = getcwd() . $dir;
            if (is_dir($dir)) {
                // return false; // Als het geen map is, kunnen we niets doen

                $files = array_diff(scandir($dir), array('.', '..'));

                foreach ($files as $file) {
                    $path = $dir . '/' . $file;

                    if (is_dir($path)) {
                        // Als het een submap is, roepen we deze functie opnieuw aan om deze te verwijderen
                        deleteDirectory($path);
                    } else {
                        // Anders verwijderen we het bestand
                        unlink($path);
                    }
                }

                // Verwijder de lege map zelf
                rmdir($dir);
            }
        }
    }

    private function addRecommendedExtensions()
    {
        $pathName         = getcwd() . '/.vscode';
        $extensions       = [
            'recommendations' => [
                'esbenp.prettier-vscode',
                'dbaeumer.vscode-eslint',
                'mblode.twig-language-2',
                'formulahendry.auto-rename-tag'
            ]
        ];
        $extensionsString = json_encode($extensions, JSON_PRETTY_PRINT);

        if (!is_dir($pathName)) {
            mkdir(($pathName), 0755, true);
        }

        // Create and write extensions json file
        if (!file_exists($pathName . '/extensions.json')) {
            touch($pathName . '/extensions.json');
        }

        $extensionsFile = fopen($pathName . '/extensions.json', 'w');
        fwrite($extensionsFile, $extensionsString);
        fclose($extensionsFile);
    }

    private function createPrettierConfig()
    {
        $pathName       = getcwd();
        $prettierConfig = [
            'trailingComma'          => 'es5',
            'tabWidth'               => 2,
            'semi'                   => true,
            'singleQuote'            => true,
            'bracketSpacing'         => true,
            'bracketSameLine'        => false,
            'requirePragma'          => true,
            'singleAttributePerLine' => true,
        ];

        if (!file_exists($pathName . '/.prettierrc.json')) {
            touch($pathName . '/.prettierrc.json');
        }

        $prettierConfigFile = fopen($pathName . '/.prettierrc.json', 'w');
        fwrite($prettierConfigFile, json_encode($prettierConfig, JSON_PRETTY_PRINT));
        fclose($prettierConfigFile);
    }

    private function installNodeDependencies()
    {
        try {
            // Install pnpm for faster package management
            passthru('npm install -g pnpm');

            // Install necessary gulp packages & sass
            passthru('pnpm install -D gulp gulp-exec gulp-watch gulp-sass gulp-concat gulp-autoprefixer del@6.1.1 gulp-uglifycss sass fs path && pnpm install -g gulp-cli');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    private function createFilesAndFolders()
    {
        try {
            passthru('touch .gitignore && echo "/node_modules\nlightspeed-settings.json" >> .gitignore');
            passthru('mkdir src && mkdir src/sass &&');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
