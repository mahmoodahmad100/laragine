<?php

namespace Yepwoo\Laragine\Logic;

use Illuminate\Support\Facades\File;

class FileManipulator
{
    protected static $main_path;

    /**
     * generate the files
     * 
     * @param  string   $source_dir
     * @param  string   $destination_dir
     * @param  string[] $files
     * @param  string[] $search  str_replace first param
     * @param  string[] $replace str_replace second param
     * @return void
     */
    public static function generate($source_dir, $destination_dir, $files, $search = [], $replace = [])
    {
        foreach ($files as $name => $destination) {
            $file      = $name;
            $full_path = $source_dir . '/' .$file;
            $content   = '';

            if (strpos($destination, '*') !== false) {
                $file        = '';
                $destination = substr($destination, 0, strrpos($destination, '*'));
            } else {
                if (strpos($destination, '.') !== false) {
                    if (strpos($destination, '/') !== false) {
                        $file        = substr($destination, strrpos($destination, '/') + 1);
                        $destination = substr($destination, 0, strrpos($destination, '/')) . '/';
                    } else {
                        $file        = $destination;
                        $destination = '';
                    }
                }
                
                if (isset($search['file']) && isset($replace['file'])) {
                    $file = str_replace($search['file'], $replace['file'], $file);
                }
    
                if (isset($search['content']) && isset($replace['content'])) {
                    $content = str_replace($search['content'], $replace['content'], file_get_contents($full_path));
                } else {
                    $content = file_get_contents($full_path);
                }
            }
            
            File::makeDirectory("$destination_dir/{$destination}", $mode = 0775, true, true);
            if ($file == '') {
                File::copyDirectory("$source_dir/{$destination}", "$destination_dir/{$destination}");
            } else {
                file_put_contents("$destination_dir/{$destination}{$file}", $content);
            }
        }
    }

    /**
     * get the schema
     * 
     * @param  string $path
     * @return array
     */
    public static function getSchema($path = __DIR__ . '/schema.json')
    {
        return self::readJson($path);
    }    

    /**
     * read json file
     * 
     * @param  string $path
     * @return array
     */
    public static function readJson($path)
    {
        $json = file_get_contents($path);
        return json_decode($json, true);
    } 

    /**
     * check if dir/file exists
     * 
     * @param  string $path
     * @return bool
     */
    public static function exists($path)
    {
        return File::exists($path);
    }

    public static function getTemplate($file, $stubs_vars, $object)
    {
        $replaced_vars = [$object['studly'], $object['plural_lower_case'], $object['singular_lower_case']];
        return str_replace(
            $stubs_vars, $replaced_vars, getStub(__DIR__ . '/../'.self::getMainPath().'/Module' . '\\' . $file)
        );
    }

    public static function generate_2($file, $stubs_vars, $object, $path)
    {
        self::setMainPath();
        $template = self::getTemplate($file, $stubs_vars, $object);

        file_put_contents(base_path() . '\\core' . "\\" . $object['studly'] . "\\$file", $template);
    }

    private static function setMainPath() {
        self::$main_path = 'core';
    }

    private static function getMainPath() {
        return self::$main_path;
    }
}
