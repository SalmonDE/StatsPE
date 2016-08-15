<?php

namespace StatsPE\Updater;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;

class UpdateTask extends AsyncTask
{
    public function __construct($url, $hash, $path, $version)
    {
        $this->url = $url;
        $this->md5hash = $hash;
        $this->path = $path;
        $this->version = $version;
    }

    public function onRun()
    {
        sleep(20);
        $newversion = file_get_contents($this->path.'version');
        if (!$newversion == 'NULL') {
            if (!$this->version == $newversion) {
                $dfile = Utils::getURL($this->url);
                if (md5($dfile) == $this->md5hash) {
                    if (file_exists('plugins/StatsPE_v'.$this->version.'.phar')) {
                        unlink('plugins/StatsPE_v'.$this->version.'.phar');
                    } else {
                        file_put_contents('StatsPEUpdaterError.log', 'Old StatsPE phar not found! Please make sure to name the StatsPE phar file like this: StatsPE_v'.$this->version.'.phar. If you are using the source folder, disable the auto-updater in the configuration file.');
                    }
                    file_put_contents('plugins/StatsPE_v'.$newversion.'.phar', $dfile);
                    $this->setResult(true);
                    if (!file_exists('plugins/StatsPE_v'.$newversion.'.phar')) {
                        $this->setResult(false);
                        file_put_contents('StatsPEUpdaterError.log', 'Error downloading phar!');
                    }
                } else {
                    $this->setResult(false);
                    file_put_contents('StatsPEUpdaterError.log', 'md5 hash of the phar was incorrect');
                }
            }
        }
    }

    public function onCompletion(Server $server)
    {
        if (file_exists($this->path.'version')) {
            unlink($this->path.'version');
        }
        if ($this->getResult) {
            $server->shutdown();
        }
    }
}
