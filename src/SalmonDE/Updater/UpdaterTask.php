<?php
namespace SalmonDE\Updater;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Utils;

class UpdaterTask extends PluginTask
{
    public function __construct($owner, $version){
        $this->name = $owner->getDescription()->getName();
        parent::__construct($owner);
        $urldata = json_decode(trim(Utils::getURL($owner->getDescription()->getWebsite().'MCPE-Plugins/Updater/Updater.php?plugin='.$owner->getDescription()->getName().'&new=1')), true);
        $this->url = $urldata['downloadurl'];
        $this->md5hash = $urldata['md5'];
        $this->version = $owner->getDescription()->getVersion();
        $this->newversion = $urldata['version'];
    }

    public function onRun($currenttick){
        $file = Utils::getURL($this->url);
        if(md5($file) == $this->md5hash){
            unlink('plugins/'.$this->name.'.phar');
            file_put_contents('plugins/'.$this->name.'.phar', $file);
            if(!file_exists('plugins/'.$this->name.'.phar')){
                $this->getOwner()->getLogger()->error('Failed to download the update!');
            }else{
                $this->getOwner()->getServer()->broadcastMessage(TF::RED.TF::BOLD.$this->getOwner()->getConfig()->get('Shutdown-Message'));
                $this->getOwner()->getServer()->broadcastTip(TF::RED.TF::BOLD.$this->getOwner()->getConfig()->get('Shutdown-Message'));
                foreach(glob("plugins/".$this->name."*.phar") as $phar){
                    unlink($phar);
                }
                sleep(7);
                $this->getOwner()->getServer()->shutdown();
            }
        }else{
            $this->owner->getLogger()->error('md5 hash of the phar was incorrect');
        }
    }
}
