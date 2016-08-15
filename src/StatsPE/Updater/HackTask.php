<?php

namespace StatsPE\Updater;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class HackTask extends PluginTask
{
    public function __construct($number){
      $this->number = $number;
    }
    public function onRun($currenttick){
        if($this->number == 0){
            $this->getOwner()->updaterHack();
        }
    }

    public function onCompletion(Server $server)
    {
        if($this->number == 1){
            $server->getScheduler()->scheduleAsyncTask(new CheckVersionTask($this->getOwner()));
        }
    }
}
