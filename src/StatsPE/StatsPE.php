<?php
namespace StatsPE;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\updater\Upgrader;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Utils;
//Events
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerFishEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
//Events

class StatsPE extends PluginBase implements Listener{

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveResource('config.yml');
		$this->checkVersion();
		$provider = $this->getConfig()->get('Provider');
		if($provider == 'JSON'){
			@mkdir($this->getDataFolder().'Stats');
		}elseif($provider == 'MySQL'){
			//Test Connection here and create database
		}else{
			$this->getLogger()->critical('Invalid provider: '.$provider.'!');
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		
	}

	public function saveData($player, $type, $data){
		$provider = $this->getConfig()->get('Provider');
		if($provider == 'JSON'){
            fwrite(fopen($this->getDataFolder().'Stats/'.$player->getName().'.json','w'), json_encode($data));
	   }elseif($provider == 'MySQL'){
			
	   }
	}

	public function getStats($player, $type){
		if($type == 'JSON'){
            return json_decode(file_get_contents($this->getDataFolder().'Stats/'.$player->getName().'.json'), true);			
		}elseif($type == 'MySQL'){
			
		}
	}

	public function onJoin(PlayerJoinEvent $event){
		$switch = $this->getConfig()->get('JoinCount');
		if($switch == true){
			$player = $event->getPlayer();
			$provider = $this->getConfig()->get('Provider');
			if($provider == 'JSON'){
			    if(file_exists($this->getDataFolder().'/Stats/'.$player->getName().'.json')){
				    $info = $this->getStats($player, 'JSON');
			   }else{
				   $pn = $player->getName();
				   $fp = $player->getFirstPlayed();
				   $cid = $player->getClientId();
				   $ip = $player->getAddress();
				   $data = array(
				       'PlayerName' => "$pn",
					   'ClientID' => "$cid",
					   'LastIP' => "$ip",
					   'FirstJoin' => "$fp",
					   'LastJoin' => "$fp",
					   'JoinCount' => '1',
					   'KillCount' => '0',
					   'DeathCount' => '0',
					   'KickCount' => '0',
					   'OnlineTime' => '0',
					   'BlocksBreaked' => '0',
					   'BlocksPlaced' => '0',
					   'ChatMessages' => '0',
					   'FishCount' => '0',
					   'EnterBedCount' => '0',
					   'EatCount' => '0',
					   'CraftCount' => '0'
				   );
				   $this->saveData($player, 'JSON',$data);
			   }
			}elseif($provider === 'MySQL'){
				
			}
		}
	}

    public function checkVersion(){
		$urldata = Utils::getURL($this->getDescription()->getWebsite().'MCPE-Plugins/'.$this->getDescription()->getName().'/Updater.php?check');
		$nversion = str_replace(' ', '', $urldata);
		$cversion = $this->getDescription()->getVersion();
		if($cversion == $nversion){
			$this->getLogger()->info(TF::GREEN.'Your '.$this->getDescription()->getName().' version ('.TF::AQUA.$cversion.TF::GREEN.') is up to date! :)');
		}else{
			$this->getLogger()->info(TF::RED.TF::BOLD.'Update available for '.$this->getDescription()->getName().'!'."\n".TF::RED.'Current version: '.$cversion."\n".TF::GREEN.TF::BOLD.'Newest version: '.$nversion);
			if($this->getConfig()->get('Auto-Update') == 'true'){
				$this->getLogger()->info('Running an update for '.$this->getDescription()->getName()."($cversion)".' to version: '.$nversion);
				$this->update();
			}else{
				$this->getLogger()->info(TF::AQUA.'Please enable "Auto-Update" inside the config file to let the plugin automatically update itself!');
			}
		}
	}

	public function update(){
		$url = Utils::getURL($this->getDescription()->getWebsite().'MCPE-Plugins/'.$this->getDescription()->getName().'/Updater.php?downloadurl');
		$md5 = Utils::getURL($this->getDescription()->getWebsite().'MCPE-Plugins/'.$this->getDescription()->getName().'/Updater.php?md5');
		$this->getLogger()->info(TF::AQUA.'MD5 Hash of the phar: '.TF::GOLD.TF::BOLD.$md5);
		$this->server->getScheduler()->scheduleAsyncTask(new Upgrader($url, $md5, $this->getDataFolder()));
	}

	public function onDisable(){
		
	}
}