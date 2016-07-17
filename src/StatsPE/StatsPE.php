<?php
namespace StatsPE;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\event\Listener;
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
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

class StatsPE extends PluginBase implements Listener{

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveResource('config.yml');
		$provider = $this->getConfig()->get('Provider');
		if($provider === 'JSON'){
			@mkdir($this->getDataFolder().'Stats');
		}elseif($provider === 'MySQL'){
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
		if($provider === 'JSON'){
            fwrite(fopen($this->getDataFolder().'Stats/'.$player->getName().'.json','w'), json_encode($data));
	   }elseif($provider === 'MySQL'){
			
	   }
	}

	public function getStats($player, $type){
		if($type === 'JSON'){
            return json_decode(file_get_contents($this->getDataFolder().'Stats/'.$player->getName().'.json'), true);			
		}elseif($type === 'MySQL'){
			
		}
	}

	public function onJoin(PlayerJoinEvent $event){
		$switch = $this->getConfig()->get('JoinCount');
		if($switch === true){
			$player = $event->getPlayer();
			$provider = $this->getConfig()->get('Provider');
			if($provider === 'JSON'){
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

	public function onDisable(){
		
	}
}