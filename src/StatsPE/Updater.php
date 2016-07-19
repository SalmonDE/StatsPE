<?php
namespace StatsPE;

use pocketmine\utils\TextFormat as TF;

class Updater{

    public function checkVersion($switch){
        getLogger()->info(TF::GREEN.$this->getDescription()->getVersion());
	}

	public function update(){
		$this->getDescription()->getWebsite();
	}
}