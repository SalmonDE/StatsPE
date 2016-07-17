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
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		
	}

	public function saveData($player, $type, $data){
		$provider = $this->getConfig()->get('Provider');
		if($provider === 'JSON'){
		    if(file_exists($this->getDataFolder().'/Stats/'.$player.'.json')){
			
		   }else{
			
		   }
	   }elseif($provider === 'MySQL'){
			
	   }else{
			$this->getLogger()->critical("Invalid Provider: $provider");
	   }
	}

	public function onJoin(PlayerJoinEvent $event){
		$switch = $this->getConfig()->get('JoinCount');
		if($switch === true){
			$player = $event->getPlayer();
			if(file_exists($this->getDataFolder().'/Stats/'.$player->getName().'.json')){
				
			}else{
				
			}
		}
	}

	public function onDisable(){
		
	}
}