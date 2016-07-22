<?php
namespace StatsPE;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
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
use StatsPE\UpdaterTask;

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
		if($cmd == 'stats' || $cmd == 'Stats'){
			if($sender->hasPermission('statspe.cmd.stats')){
				if(count($args) == 0){
					$this->showStats($sender, $sender->getName());
					return true;
				}elseif(count($args) == 1){
					$this->showStats($sender, $args[0]);
					return true;
				}else{
					$sender->sendMessage(TF::RED.'Too many arguments!');
					return false;
				}
		    }else{
				$sender->sendMessage(TF::RED.'You are not allowed to use this command!');
				return true;
			}
		}
	}

	public function saveData($player, $data){
		if($this->getConfig()->get('Provider') == 'JSON'){
            fwrite(fopen($this->getDataFolder().'Stats/'.strtolower($player->getName()).'.json','w'), json_encode($data, JSON_PRETTY_PRINT));
	   }elseif($this->getConfig()->get('Provider') == 'MySQL'){
			
	   }
	}

	public function getStats($player, $type, $data){
		if($type == 'JSON'){
            return json_decode(file_get_contents($this->getDataFolder().'Stats/'.strtolower($player).'.json'), true);
		}elseif($type == 'MySQL'){
			
		}
	}

	public function showStats($requestor, $target){
		if($target == 'CONSOLE'){
			$requestor->sendMessage(TF::RED.'You can not get the statistics of the Console!');
		}else{
		    if($this->getConfig()->get('Provider') == 'JSON'){
		        if(file_exists($this->getDataFolder().'Stats/'.strtolower($target).'.json')){
			        $info = $this->getStats($target, 'JSON', 'all');
				    $requestor->sendMessage(TF::GOLD.'---Statistics for: '.TF::GREEN.$info['PlayerName'].TF::GOLD.'---');
				    if($requestor->hasPermission('statspe.cmd.stats.advancedinfo')){
					    $requestor->sendMessage(TF::AQUA.'Last ClientID: '.TF::LIGHT_PURPLE.$info['ClientID']);
					    $requestor->sendMessage(TF::AQUA.'Last IP: '.TF::LIGHT_PURPLE.$info['LastIP']);
				    }
				    $requestor->sendMessage(TF::AQUA.'First Join: '.TF::LIGHT_PURPLE.$info['FirstJoin']);
				    $requestor->sendMessage(TF::AQUA.'Last Join: '.TF::LIGHT_PURPLE.$info['LastJoin']);
				    $requestor->sendMessage(TF::AQUA.'Total Joins: '.TF::LIGHT_PURPLE.$info['JoinCount']);
			    	$requestor->sendMessage(TF::AQUA.'Kills: '.TF::LIGHT_PURPLE.$info['KillCount']);
			    	$requestor->sendMessage(TF::AQUA.'Deaths: '.TF::LIGHT_PURPLE.$info['DeathCount']);
			    	$requestor->sendMessage(TF::AQUA.'Kicks: '.TF::LIGHT_PURPLE.$info['KickCount']);
			    	$requestor->sendMessage(TF::AQUA.'Online Time: '.TF::LIGHT_PURPLE.$info['OnlineTime']);
			    	$requestor->sendMessage(TF::AQUA.'Breaked Blocks: '.TF::LIGHT_PURPLE.$info['BlocksBreaked']);
			    	$requestor->sendMessage(TF::AQUA.'Placed Blocks: '.TF::LIGHT_PURPLE.$info['BlocksPlaced']);
			    	$requestor->sendMessage(TF::AQUA.'Chat Messages: '.TF::LIGHT_PURPLE.$info['ChatMessages']);
			    	$requestor->sendMessage(TF::AQUA.'Catched Fishes: '.TF::LIGHT_PURPLE.$info['FishCount']);
			    	$requestor->sendMessage(TF::AQUA.'Went to bed for: '.TF::LIGHT_PURPLE.$info['EnterBedCount'].TF::AQUA.' times');
			    	$requestor->sendMessage(TF::AQUA.'Ate something for: '.TF::LIGHT_PURPLE.$info['EatCount'].TF::AQUA.' times');
			    	$requestor->sendMessage(TF::AQUA.'Crafted something for: '.TF::LIGHT_PURPLE.$info['CraftCount'].TF::AQUA.' times');
		        }else{
			        $requestor->sendMessage(TF::RED.'No Stats found for: '.TF::GOLD.$target."\n".TF::AQUA.'Please check your spelling.');//Aericio please make this message nicer
		        }
		    }elseif($this->getConfig()->get('Provider') == 'MySQL'){
				
			}
		}
	}

	public function onJoin(PlayerJoinEvent $event){
		if($this->getConfig()->get('JoinCount')){
			$player = $event->getPlayer();
			$provider = $this->getConfig()->get('Provider');
			if($provider == 'JSON'){
			    if(file_exists($this->getDataFolder().'/Stats/'.$player->getName().'.json')){
					$pn = $player->getName();
				    $info = $this->getStats($player->getName(), 'JSON', 'all');
				    $cid = $player->getClientId();
				    $ip = $player->getAddress();
					$ls = date($this->getConfig()->get('TimeFormat'));
					$jc = $info['JoinCount'] + 1;
				    $data = array(
				        'PlayerName' => $pn,
					    'ClientID' => $cid,
					    'LastIP' => $ip,
					    'FirstJoin' => $info['FirstJoin'],
					    'LastJoin' => $ls,
					    'JoinCount' => $jc,
					    'KillCount' => $info['KillCount'],
					    'DeathCount' => $info['DeathCount'],
					    'KickCount' => $info['KickCount'],
					    'OnlineTime' => $info['OnlineTime'],
					    'BlocksBreaked' => $info['BlocksBreaked'],
					    'BlocksPlaced' => $info['BlocksPlaced'],
					    'ChatMessages' => $info['ChatMessages'],
					    'FishCount' => $info['FishCount'],
					    'EnterBedCount' => $info['EnterBedCount'],
					    'EatCount' => $info['EatCount'],
					    'CraftCount' => $info['CraftCount']
				    );
				    $this->saveData($player, $data);
			    }else{
				    $pn = $player->getName();
				    $fp = date($this->getConfig()->get('TimeFormat'));
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
					    'OnlineTime' => 'WIP',
					    'BlocksBreaked' => '0',
					    'BlocksPlaced' => '0',
					    'ChatMessages' => '0',
					    'FishCount' => '0',
					    'EnterBedCount' => '0',
					    'EatCount' => '0',
					    'CraftCount' => '0'
				    );
				    $this->saveData($player, $data);
			    }
		    }elseif($provider === 'MySQL'){
				
			}
		}
	}

    public function checkVersion(){
		$urldata = Utils::getURL($this->getDescription()->getWebsite().'MCPE-Plugins/'.$this->getDescription()->getName().'/Updater.php?check');
		$nversion = str_replace(array(" ", "\r", "\n"), '', $urldata);
		$cversion = $this->getDescription()->getVersion();
		if($cversion == $nversion){
			$this->getLogger()->info(TF::GREEN.'Your '.$this->getDescription()->getName().' version ('.TF::AQUA.$cversion.TF::GREEN.') is up to date! :)');
		}else{
			$this->getLogger()->info(TF::RED.TF::BOLD.'Update available for '.$this->getDescription()->getName().'!'."\n".TF::RED.'Current version: '.$cversion."\n".TF::GREEN.TF::BOLD.'Newest version: '.$nversion);
			if($this->getConfig()->get('Auto-Update') == 'true'){
				$this->getLogger()->info('Running an update for '.$this->getDescription()->getName()."($cversion)".' to version: '.$nversion);
				$this->update($nversion);
			}else{
				if($this->isPhar()){
				    $this->getLogger()->info(TF::AQUA.'Please enable "Auto-Update" inside the config file to let the plugin automatically update itself!');
				}else{
					$this->getLogger()->info(TF::AQUA.TF::BOLD.'Looks like you do not use a phar file of this plugin. You can still use the Auto Updater but it will not delete the source code. Please delete it by yourself if you update.');
				}
			}
		}
	}

	public function update($newversion){
		$url = Utils::getURL($this->getDescription()->getWebsite().'MCPE-Plugins/'.$this->getDescription()->getName().'/Updater.php?downloadurl');
		$md5 = Utils::getURL($this->getDescription()->getWebsite().'MCPE-Plugins/'.$this->getDescription()->getName().'/Updater.php?md5');
		$this->getLogger()->info(TF::AQUA.'MD5 Hash of the phar: '.TF::GOLD.TF::BOLD.$md5);
		$this->getServer()->getScheduler()->scheduleAsyncTask(new UpdaterTask($url, $md5, $this->getDataFolder(), $this->getDescription()->getVersion(), $newversion));
	}

	public function onDisable(){
		
	}
}