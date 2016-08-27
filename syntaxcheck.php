<?php
$base = glob("plugins/StatsPE-src/src/*.php");
$updater = glob("plugins/StatsPE-src/src/Updater/*.php");
$tasks = glob("plugins/StatsPE-src/src/Tasks/*.php");
$files = array_merge($base, $updater, $tasks);
foreach($files as $file){
    exec("php -l $file", $output);
}
foreach($output as $line){
    echo($line);
}
