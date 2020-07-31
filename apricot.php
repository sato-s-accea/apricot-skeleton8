<?php
require __DIR__.'/vendor/autoload.php';

if ($argc != 2)
{
    show_usage();
    exit(0);
}

$config = require 'apricot.config.php';

switch(strtolower($argv[1]))
{
    case 'build':
        build($config);
        break;

    case 'clean':
        clean($config);
        break;

    default:
        show_usage($config);
}

/**
 * Builds a new skeleton application
 */
function build(array $config)
{
    echo "Start to build ... \n";

    $mode = (int)array_get($config,'mode',0777);

    walk($config,
        function($src_dir, $dst_dir) use($mode) {
            if (!file_exists($dst_dir))
            {
                recursive_copy($src_dir, $dst_dir, $mode);
                echo " Created $dst_dir.\n";
            }
        },
        function($srs_file , $dst_file){
            if (!file_exists($dst_file))
            {
                @copy( $srs_file , $dst_file);
                echo " Created $dst_file.\n";
            }
        },
    );

    echo "Done.";
}

/**
 * Cleans up an existing skeleton application
 */
function clean(array $config)
{
    echo "Start to clean ... \n";

    walk($config,
        function($src_dir, $dst_dir){
            if (file_exists($dst_dir))
            {
                recursive_delete($dst_dir);
                echo " Deleted $dst_dir.\n";
            }
        },
        function($srs_file , $dst_file){
            if (file_exists($dst_file))
            {
                @unlink($dst_file);
                echo " Deleted $dst_file.\n";
            }
        },
    );

    echo "Done.";
}
/**
 * Walks along the skeleton.
 */
function walk(array $config, callable $dir_func, callable $file_func)
{
    $src_base_dir = add_path(__DIR__, array_get($config,'source','vendor/y2sunlight/apricot'));
    $dst_base_dir = __DIR__;

    // directorys
    $directories = array_get($config,'directories',array());
    foreach($directories as $dir)
    {
        $dst_dir = add_path($dst_base_dir, $dir);
        $src_dir = add_path($src_base_dir, $dir);
        $dir_func($src_dir, $dst_dir);
    }

    // files
    $files = array_get($config,'files',array());
    foreach($files as $srs=>$dst)
    {
        $srs_file = add_path($src_base_dir, $srs);
        $dst_file = add_path($dst_base_dir, $dst);

        $file_func($srs_file, $dst_file);
    }
}

/**
 * Shows usage for this command
 */
function show_usage()
{
    echo <<<EOT

Usage: php apricot.php build | clean

< Arguments >
 build --- Builds a new skeleton application.
 clean --- Cleans up your application that was built.

EOT;
}

