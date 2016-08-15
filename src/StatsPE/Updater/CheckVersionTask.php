<?php
namespace StatsPE\Updater;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Utils;
use StatsPE\StatsPE;

class CheckVersionTask extends AsyncTask
{

	public function __construct(StatsPE $owner){
		$this->name = $owner->getDescription()->getName();
		$this->cversion = $owner->getDescription()->getVersion();
		$this->website = $owner->getDescription()->getWebsite();
		$this->autoupdate = $owner->getConfig()->get('Auto-Update');
	}

	public function onRun(){
		$nversion = Utils::getURL($this->website.'MCPE-Plugins/'.$this->name.'/Updater.php?check');
		$nversion = str_replace(array(" ", "\r", "\n"), '', $nversion);
		$cversion = $this->cversion;
		if(!$nversion){
			//$this->getLogger()->warning(TF::RED.'Checking for Update Failed: Empty Response');
		}else{
        $this->setResult($nversion);
		}
	}

	public function onCompletion(Server $server){
		if(!$this->cversion == $this->getResult()){
			$server->broadcastMessage(TF::GREEN.'Running an update for '.$this->name." ($this->cversion)".' to version: '.$this->nversion);
			StatsPE::update($this->nversion);
		}
	}
}
