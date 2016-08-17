<?php

namespace StatsPE\Updater;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Utils;

class UpdaterTask extends PluginTask
{
    public function __construct($url, $hash, $path, $version, $nversion, $owner)
    {
        $this->owner = $owner;
        parent::__construct($owner);
        $this->url = $url;
        $this->md5hash = $hash;
        $this->path = $path;
        $this->version = $version;
        $this->newversion = $nversion;
    }

    public function onRun($currenttick)
    {
        $dfile = Utils::getURL($this->url);
        if (md5($dfile) == $this->md5hash) {
            if (file_exists('plugins/StatsPE_v'.$this->version.'.phar')) {
                unlink('plugins/StatsPE_v'.$this->version.'.phar');
            } else {
                $this->owner->getLogger()->warning('Old StatsPE phar not found! Please make sure to name the StatsPE phar file like this: StatsPE_v'.$this->version.'.phar. If you are using the source folder, disable the auto-updater in the configuration file.');
            }
            file_put_contents('plugins/StatsPE_v'.$this->newversion.'.phar', $dfile);
            $this->setResult(true);
            if (!file_exists('plugins/StatsPE_v'.$this->newversion.'.phar')) {
                $this->setResult(false);
                $this->owner->getLogger()->warning('Error downloading phar!');
            }
        } else {
            $this->setResult(false);
            $this->owner->getLogger()->warning('md5 hash of the phar was incorrect');
        }
    }

    public function onCompletion(Server $server){
        if ($this->getResult) {
            $server->broadcastMessage(TF::RED.RF::BOLD.'Restarting Server due to a software update!');
            $server->broadcastTip(TF::RED.RF::BOLD.'Restarting Server due to a software update!');
            sleep(2);
            $server->shutdown();
        }
    }
}
