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
		    if($cversion == $nversion){
			    //$this->owner->getLogger()->info(TF::GREEN.'Your '.$this->owner->getDescription()->getName().' version ('.TF::AQUA.$cversion.TF::GREEN.') is up to date! :)');
		    }else{
			    //$this->owner->getLogger()->notice(TF::RED.TF::BOLD.'Update available for '.$this->owner->getDescription()->getName().'!'."\n".TF::RED.'Current version: '.$cversion."\n".TF::GREEN.TF::BOLD.'Newest version: '.$nversion);
			    if($this->autoupdate){
				    //$this->owner->getLogger()->warning(TF::GREEN.'Will run an update for '.$this->owner->getDescription()->getName()."($cversion)".' to version: '.$nversion);
					$this->setResult(true);
				}/*else{
				    if($this->owner->isPhar()){
				        $this->owner->getLogger()->info(TF::AQUA.'Please set "Auto-Update" to "true" to automatically update the plugin!');
				    }else{
					    $this->owner->getLogger()->info(TF::AQUA.TF::BOLD.'It seems that you are not using a StatsPE phar. It still updates, but the source folder will not be removed. Please delete the file manually to prevent errors.');
				    }
			    }*/
		    }
		}
	}

	public function onCompletion(Server $server){
		if($this->getResult()){
			$this->owner->getLogger()->warning(TF::GREEN.'Will run an update for '.$this->owner->getDescription()->getName()." ($cversion)".' to version: '.$nversion);
			$this->owner->update($nversion);
		}
	}
}
