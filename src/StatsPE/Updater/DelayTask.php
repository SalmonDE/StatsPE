<?php
namespace StatsPE\Updater;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class DelayTask extends PluginTask
{
    public function __construct($owner){
      $this->owner = $owner;
      parent::__construct($owner);
    }

     public function onRun($currenttick){}

     public function onCompletion(Server $server){
             $server->getScheduler()->scheduleAsyncTask(new CheckVersionTask($this->owner));
     }
 }
