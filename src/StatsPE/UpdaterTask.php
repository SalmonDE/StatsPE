<?php
namespace StatsPE;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Utils;

class UpdaterTask extends AsyncTask{

	public function __construct($url, $hash, $path, $version, $newversion){
		$this->url = $url;
		$this->md5hash = $hash;
		$this->path = $path;
		$this->version = $version;
		$this->newversion = $newversion;
	}

	public function onRun(){
		$dfile = Utils::getURL($this->url);
		if(file_exists('plugins\StatsPE_v'.$this->version.'.phar')){
		    if(md5($dfile) == $this->md5hash){
				file_put_contents('plugins\StatsPE_v'.$this->newversion.'.phar', $dfile);
			    unlink('plugins\StatsPE_v'.$this->version.'.phar');
				if(file_exists('plugins\StatsPE_v'.$this->version.'.phar')){
					file_put_contents('StatsPEUpdaterError.log', 'Could not delete old phar file!');
				}elseif(!file_exists('plugins/StatsPE_v'.$this->newversion.'.phar')){
					file_put_contents('StatsPEUpdaterError.log', 'Could not download new phar file!');
				}
		   }else{
			    file_put_contents('StatsPEUpdaterError.log', 'md5 hash of the downloaded file was not correct');
		   }
		}else{
			file_put_contents('StatsPEUpdaterError.log', 'Old StatsPE phar not found! Please make sure to name the StatsPE phar file like this: StatsPE_v'.$this->version.'.phar or if you use the source code disable the Auto Updater to prevent errors');
		}
	}
}