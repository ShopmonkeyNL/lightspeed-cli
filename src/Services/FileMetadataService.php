<?php

namespace ShopmonkeyNL\ShopmonkeyCli\Services;
use ShopmonkeyNL\ShopmonkeyCli\Services\InputOutput;

class FileMetadataService
{
    
    public $file;

    public function __construct() {
        $this->file = getcwd() . '/files-metadata.json';
    }

    public function get($key = false) {

        $file = $this->file;

        if (!file_exists($file)) {
            file_put_contents($file, json_encode([], JSON_PRETTY_PRINT));
        }
        $current_data = file_get_contents($file); 
        $current_data = json_decode($current_data,true); 

        if (!$key) {
            return $current_data;
        } elseif (isset($current_data[$key])) {
            return $current_data[$key];
        } else {
            return false;
        }
        

    }

    public function update($key, $updated_at) {

        $file = $this->file;
        $current_data = $this->get();

        $new_data = $current_data;
        $new_data[$key] = $updated_at;

        file_put_contents($file, json_encode($new_data, JSON_PRETTY_PRINT));

        return $new_data;

    }

}