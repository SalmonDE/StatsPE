<?php
namespace StatsPE;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;

class UpdaterTask extends AsyncTask
{

	public function __construct($url, $hash, $path, $version, $newversion){
		$this->url = $url;
		$this->md5hash = $hash;
		$this->path = $path;
		$this->version = $version;
		$this->newversion = $newversion;
	}

	public function onRun(){
		$dfile = Utils::getURL($this->url);
		    if(md5($dfile) == $this->md5hash){
				if(file_exists("plugins/StatsPE_v".$this->version.'.phar')){
			        unlink("plugins/StatsPE_v".$this->version.'.phar');
			    }else{
					file_put_contents('StatsPEUpdaterError.log', 'Old StatsPE phar not found! Please make sure to name the StatsPE phar file like this: StatsPE_v'.$this->version.'.phar. If you are using the source folder, disable the auto-updater in the configuration file.');
				}
				file_put_contents("plugins/StatsPE_v".$this->newversion.'.phar', $dfile);
			    if(!file_exists("plugins/StatsPE_v".$this->newversion.'.phar')){
					file_put_contents('StatsPEUpdaterError.log', 'Error downloading phar!');
			    }
		    }else{
			    file_put_contents('StatsPEUpdaterError.log', 'md5 hash of the phar was incorrect');
		    }
    }

	public function onCompletion(Server $server){
		$server->shutdown();
	}
}
