<?php


require_once __DIR__.'/vendor/autoload.php';
if(count($argv) === 1) $argv[1] = __DIR__."/http.json";

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

use com\github\tncrazvan\Orm\Examples\Example1\ExampleJoin;
use com\github\tncrazvan\Orm\Examples\Example1\ExampleSelect;

$test = new ExampleJoin();
$test->main();