<?php
namespace StatsPE\Updater;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Utils;

class CheckVersionTask extends AsyncTask
{
    public function __construct($owner){
        $this->name = $owner->getDescription()->getName();
        $this->cversion = $owner->getDescription()->getVersion();
        $this->website = $owner->getDescription()->getWebsite();
        $this->autoupdate = $owner->getConfig()->get('Auto-Update');
        $this->path = $owner->getDataFolder();
    }

    public function onRun(){
        $url = Utils::getURL($this->website.'MCPE-Plugins/Updater/Updater.php?plugin='.$this->name.'&type=version', 20);
        $nversion = str_replace(array(' ', "\r", "\n"), '', $url);
        if($nversion){
            if(!$this->cversion == $nversion){
                Server::getInstance()->getPluginManager()->getPlugin($this->name)->getLogger()->notice(TF::GOLD.'Update available for '.$this->name.'!');
                Server::getInstance()->getPluginManager()->getPlugin($this->name)->getLogger()->notice(TF::RED.'Current version: '.$this->cversion);
                Server::getInstance()->getPluginManager()->getPlugin($this->name)->getLogger()->notice(TF::GREEN.'New Version: '.$nversion);
                $this->setResult($nversion);
            }
        }else{
            $this->getOwner()->getLogger()->warning(TF::RED.'Could not check for Update: "Empty Response" !');
            $this->setResult(false);
        }
   }

    public function onCompletion(Server $server){
        if($this->getResult()){
            $server->getPluginManager()->getPlugin($this->name)->update($this->getResult());
        }else{
            $server->getPluginManager()->getPlugin($this->name)->getLogger()->notice('Auto Updater failed!');
        }
    }
}
