<?php

namespace App\Helpers;

use App\Exceptions\ConfigFileNotFoundException;

class Config
{
    public static function getFileContents(string $filename)
    {
        $filepath = realpath(__DIR__ . '/../configs/' . $filename . '.php');

        if(!$filepath){
            throw new ConfigFileNotFoundException();
        }

        $fileContent = require $filepath;

        return $fileContent;
    }

    public static function get($filename, $key = null)
    {
        $fileContent = self::getFileContents($filename);

        if(is_null($key)) return $fileContent;

        return $fileContent[$key] ?? null;
    }
}