<?php
namespace StatsPE\Updater;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class HackTask extends PluginTask
{
    public function __construct(){}

     public function onRun($currenttick){}

     public function onCompletion(Server $server){
             $server->getScheduler()->scheduleAsyncTask(new CheckVersionTask($this->getOwner()));
     }
 }
