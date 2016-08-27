<?php
$base = glob('plugins/StatsPE-src/*.php');
$updater = glob('plugins/StatsPE-src/Updater/*.php');
$tasks = glob('plugins/StatsPE-src/Tasks/*.php');
$files = array_merge($base, $updater, $tasks);
var_dump($base);
var_dump($updater);
var_dump($tasks);
var_dump($files);
foreach($files as $file){
    exec("php -l $file", $output);
}
//foreach($output as $line){
    //echo($line);
//}
