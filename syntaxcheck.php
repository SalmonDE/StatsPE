<?php
$base = glob('plugins/StatsPE-src/*.php');
$updater = glob('plugins/StatsPE-src/Updater/*.php');
$tasks = glob('plugins/StatsPE-src/Tasks/*.php');
$phpfiles = array_merge($base, $updater, $tasks);
foreach($phpfiles as $file){
    exec("php -l $file", $output);
}
foreach($output as $line){
    echo($line);
}
