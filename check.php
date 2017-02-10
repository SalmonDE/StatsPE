<?php
//Syntax
foreach(getFiles(__DIR__) as $file){
    echo $output[$file] = shell_exec('php -l '.$file);
}
foreach($output as $k => $string){
    if(trim($string) !== 'No syntax errors detected in '.$k){
        echo('Syntax error found in: '.$k.PHP_EOL);
        echo($string.PHP_EOL);
        exit(1);
    }
}
exit(0);

function getFiles(string $path) : array{
    $files = scandir($path);
    foreach($files as $k => $file){
        if(is_dir($file) && !in_array($file, ['.', '..'])){
            foreach(getFiles($file) as $f){
                $files[] = $file.DIRECTORY_SEPARATOR.$f;
            }
        }
        if(pathinfo($file, PATHINFO_EXTENSION) !== 'php'){
            unset($files[$k]);
        }
    }
    return $files;
}
//Syntax
