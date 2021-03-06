#!/usr/bin/php
<?php
chdir(dirname(__FILE__).'/..');
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
ini_set('memory_limit','-1');
require 'vendor/autoload.php';

use net\razshare\catpaw\attributes\metadata\Meta;
use net\razshare\catpaw\CatPaw;
use net\razshare\catpaw\tools\Dir;
use net\razshare\catpaw\tools\helpers\Factory;
use React\EventLoop\LoopInterface;

$_files_last_changed = [];

$dirname = dirname(__FILE__).'/..';

chdir($dirname);

function check_file_change(){
    global $_files_last_changed,$dirname;
    chdir($dirname);
    $files = [];
    Dir::findFilesRecursive("$dirname/src",$files);

    $tmp = $_files_last_changed;

    foreach($files as &$file){
        $name = $file['name'];
        if(isset($_files_last_changed[$name])){
            if($_files_last_changed[$name] < $file['lastChange']){
                echo "Restarting server...\n";
                exit;
            }
        }
        $_files_last_changed[$name] = $file['lastChange'];
        unset($tmp[$name]);
    }
    $c = count($tmp);
    if($c > 0){
        echo "Restarting server...\n";
        exit;
    }
}
function start():void{
    global $server,$argv,$config;
    if(isset($argv[1]) && $argv[1] === 'dev'){
        $delay = isset($argv[2])?\intval($argv[2]):100;
        while(1){
            $pid = pcntl_fork();
            if ($pid == -1) {
                die('Could not fork process'.PHP_EOL);
            } else if ($pid) {
                // we are the parent
                pcntl_wait($status); //Protect against Zombie children
            } else {
                opcache_reset();
                check_file_change();
                Meta::reset();
                $config = include('catpaw.config.php');
                $conf = $config();
                $loop = Factory::make(LoopInterface::class);
                if($loop instanceof LoopInterface)
                    $loop->addPeriodicTimer($delay/1000,fn()=>check_file_change());
                
                $server = new CatPaw($conf, $loop);
            }
        }
    }else {
        $config = require('catpaw.config.php');
        $conf = $config();
        $loop = Factory::make(LoopInterface::class);
        $server = new CatPaw($conf, $loop);
    }

    

}

start();