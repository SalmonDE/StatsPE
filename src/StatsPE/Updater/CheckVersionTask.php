<?php

namespace StatsPE\Updater;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Utils;

class CheckVersionTask extends AsyncTask
{
    public function __construct($owner)
    {
        $this->name = $owner->getDescription()->getName();
        $this->cversion = $owner->getDescription()->getVersion();
        $this->website = $owner->getDescription()->getWebsite();
        $this->autoupdate = $owner->getConfig()->get('Auto-Update');
        $this->path = $owner->getDataFolder();
    }

    public function onRun()
    {
        $nversion = str_replace(array(' ', "\r", "\n"), '', Utils::getURL($this->website.'MCPE-Plugins/'.$this->name.'/Updater.php?check'));
        $cversion = $this->cversion;
        if (!$nversion) {
            $this->getOwner()->getLogger()->warning(TF::RED.'Could not check for Update: "Empty Response" !');
            $this->setResult(false);
        } else {
            if (!$this->cversion == $nversion) {
                $server->getPluginManager()->getPlugin($this->name)->getLogger()->notice(TF::GOLD.'Update available for '.$this->name.'!');
                $server->getPluginManager()->getPlugin($this->name)->getLogger()->notice(TF::RED.'Current version: '.$this->cversion);
                $server->getPluginManager()->getPlugin($this->name)->getLogger()->notice(TF::GREEN.'New Version: '.$nversion);
                $this->setResult($nversion);
            }
        }
    }

    public function onCompletion(Server $server)
    {
        if (!$this->getResult()) {
            $server->getPluginManager()->getPlugin($this->name)->getLogger()->notice('Make sure to check the version on your own. Perhaps the Updater has an issue.');
        } else {
            $server->getPluginManager()->getPlugin($this->name)->update($this->getResult());
        }
    }
}
