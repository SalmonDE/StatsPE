<?php
namespace SalmonDE;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Utils;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerFishEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use SalmonDE\Tasks\FixTableTask;
use SalmonDE\Tasks\SaveDataTask;
use SalmonDE\Tasks\ShowStatsTask;
use SalmonDE\Tasks\SpawnFloatStatTask;
use SalmonDE\Updater\CheckVersionTask;
use SalmonDE\Updater\UpdaterTask;

class StatsPE extends PluginBase implements Listener
{

    public function onEnable(){
      @mkdir($this->getDataFolder());
      $this->saveResource('config.yml');
      $this->saveResource(strtolower($this->getConfig()->get('Language')).'.ini');
      rename($this->getDataFolder().strtolower($this->getConfig()->get('Language')).'.ini', $this->getDataFolder().'messages.ini');
        if(!$this->getServer()->getName() === 'ClearSky'){
            $this->getLogger()->warning(TF::RED.$this->getMessages('General')['NotSupported']);
        }
        $provider = strtolower($this->getConfig()->get('Provider'));
        if($provider == 'json'){
            @mkdir($this->getDataFolder().'Stats');
        }elseif($provider == 'mysql') {
            $mysql = $this->getConfig()->get('MySQL');
            $connection = @mysqli_connect($mysql['host'], $mysql['user'], $mysql['password']);
            if($connection){
                $this->getLogger()->notice(TF::GREEN.$this->getMessages('MySQL')['ConnectSuccess']);
                $yes = $this->getMessages('Player')['StatYes'];
                $no = $this->getMessages('Player')['StatNo'];
                $table = "CREATE TABLE Stats (
                PlayerName VARCHAR(16) NOT NULL UNIQUE,
                Online VARCHAR(10) NOT NULL DEFAULT '$yes',
                ClientID VARCHAR(30) NOT NULL,
                UUID VARCHAR(30),
                XBoxAuthenticated CHAR(10) NOT NULL DEFAULT '$no',
                LastIP VARCHAR(15) NOT NULL,
                FirstJoin VARCHAR(30) NOT NULL,
                LastJoin VARCHAR(30) NOT NULL,
                JoinCount INT(255) NOT NULL DEFAULT 0,
                KillCount INT(255) UNSIGNED DEFAULT 0,
                DeathCount INT(255) UNSIGNED DEFAULT 0,
                KickCount INT(255) UNSIGNED DEFAULT 0,
                OnlineTime INT(255) UNSIGNED DEFAULT 0,
                BlocksBreaked INT(255) UNSIGNED DEFAULT 0,
                BlocksPlaced INT(255) UNSIGNED DEFAULT 0,
                ChatMessages INT(255) UNSIGNED DEFAULT 0,
                FishCount INT(255) UNSIGNED DEFAULT 0,
                EnterBedCount INT(255) UNSIGNED DEFAULT 0,
                EatCount INT(255) UNSIGNED DEFAULT 0,
                CraftCount INT(255) UNSIGNED DEFAULT 0,
                DroppedItems INT(255) UNSIGNED DEFAULT 0
                )";
                if(mysqli_select_db($connection, $mysql['database'])){
                    if(!mysqli_query($connection, "SELECT * FROM Stats")){
                        if(mysqli_query($connection, $table)){
                            $this->getLogger()->notice($this->getMessages('MySQL')['CreateTableSuccess'].$mysql['database']);
                        }else{
                            $this->getLogger()->critical($this->getMessages('MySQL')['CreateTableFailure'].mysqli_error($connection));
                        }
                    }
                }else{
                    $this->getLogger()->notice($this->getMessages('MySQL')['NotFoundDatabase']);
                    if(mysqli_query($connection, 'CREATE DATABASE '.$mysql['database'])){
                        $this->getLogger()->notice($this->getMessages('MySQL')['CreateDatabaseSuccess'].'('.$mysql['database'].')');
                        if(mysqli_query($connection, $table)){
                            $this->getLogger()->info($this->getMessages('MySQL')['CreateTableSuccess'].$mysql['database']);
                        }else{
                            $this->getLogger()->critical($this->getMessages('MySQL')['CreateTableFailure'].mysqli_error($connection));
                        }
                    }else{
                        $this->getLogger()->critical($this->getMessages('MySQL')['CreateDatabaseFailure'].mysqli_error($connection));
                    }
                }
                mysqli_close($connection);
            }else{
                $this->getLogger()->critical(TF::RED.TF::BOLD.$this->getMessages('MySQL')['ConnectFailure'].mysqli_connect_error());
            }
        }else{
            $this->getLogger()->critical($this->getMessages('MySQL')['ProviderInvalid'].$provider);
        }
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getScheduler()->scheduleAsyncTask(new CheckVersionTask($this));
    }

    public function getMessages($category = false, $line = false){
        if(file_exists($this->getDataFolder().'messages.ini')){
            $lines = parse_ini_file($this->getDataFolder().'messages.ini', true);
            if(is_array($lines)){
                if($category){
                    if($line){
                        return $lines[$category][$line];
                    }else{
                        return $lines[$category];
                    }
                }else{
                    return $lines;
                }
            }else{
                return 'Error: Can not return an array';
            }
        }else{
            $this->saveResource(strtolower($this->getConfig()->get('Language')).'.ini');
            rename($this->getDataFolder().strtolower($this->getConfig()->get('Language')).'.ini', $this->getDataFolder().'messages.ini');
        }
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        if(strtolower($cmd) == 'stats'){
            if(count($args) == 0){
                $this->showStats($sender, $sender->getName());
                return true;
            }elseif(count($args) == 1){
                $this->showStats($sender, $args[0]);
                return true;
            }else{
                $sender->sendMessage(TF::RED.$this->getMessages('Player')['CommandErrorTooManyArguments']);
                return false;
            }
        }elseif(strtolower($cmd) == 'floatingstats'){
            if(count($args) >= 1){
                if($args[0] == 'add'){
                    if($sender instanceof Player){
                        if(count($args) == 2){
                            $fstat = [
                                'Name' => $args[1],
                                'Enabled' => true,
                                'Position' => [
                                    'X' => $sender->getX(),
                                    'Y' => $sender->getY() + 1,
                                    'Z' => $sender->getZ(),
                                    'Level' => $sender->getLevel()->getName()
                                ],
                                'PlayerName' => false,
                                'Stats' => [
                                    'Online' => [
                                        'Name' => 'Online',
                                        'Lang' => 'StatOnline',
                                        'Enabled' => false
                                    ],
                                    'FirstJoin' => [
                                        'Name' => 'FirstJoin',
                                        'Lang' => 'StatFirstJoin',
                                        'Enabled' => true
                                    ],
                                    'LastJoin' => [
                                        'Name' => 'LastJoin',
                                        'Lang' => 'StatLastJoin',
                                        'Enabled' => true
                                    ],
                                    'JoinCount' => [
                                        'Name' => 'JoinCount',
                                        'Lang' => 'StatJoinCount',
                                        'Enabled' => true
                                    ],
                                    'KillCount' => [
                                        'Name' => 'KillCount',
                                        'Lang' => 'StatKillCount',
                                        'Enabled' => true
                                    ],
                                    'DeathCount' => [
                                        'Name' => 'DeathCount',
                                        'Lang' => 'StatDeathCount',
                                        'Enabled' => true
                                    ],
                                    'K/D' => [
                                        'Name' => 'K/D',
                                        'Lang' => 'StatK/D',
                                        'Enabled' => true
                                    ],
                                    'KickCount' => [
                                        'Name' => 'KickCount',
                                        'Lang' => 'StatKickCount',
                                        'Enabled' => true
                                    ],
                                    'OnlineTime' => [
                                        'Name' => 'OnlineTime',
                                        'Lang' => 'StatOnlineTime',
                                        'Enabled' => false,
                                    ],
                                    'BlockBreakCount' => [
                                        'Name' => 'BlocksBreaked',
                                        'Lang' => 'StatBlockBreakCount',
                                        'Enabled' => true
                                    ],
                                    'BlockPlaceCount' => [
                                        'Name' => 'BlocksPlaced',
                                        'Lang' => 'StatBlockPlaceCount',
                                        'Enabled' => true
                                    ],
                                    'ChatCount' => [
                                        'Name' => 'ChatMessages',
                                        'Lang' => 'StatChatMessageCount',
                                        'Enabled' => true
                                    ],
                                    'FishCount' => [
                                        'Name' => 'FishCount',
                                        'Lang' => 'StatFishCount',
                                        'Enabled' => true
                                    ],
                                    'BedEnterCount' => [
                                        'Name' => 'EnterBedCount',
                                        'Lang' => 'StatBedEnterCount',
                                        'Enabled' => true
                                    ],
                                    'EatCount' => [
                                        'Name' => 'EatCount',
                                        'Lang' => 'StatEatCount',
                                        'Enabled' => true
                                    ],
                                    'CraftCount' => [
                                        'Name' => 'CraftCount',
                                        'Lang' => 'StatCraftCount',
                                        'Enabled' => true
                                    ],
                                    'DroppedItems' => [
                                        'Name' => 'DroppedItems',
                                        'Lang' => 'StatDroppedItems',
                                        'Enabled' => true
                                    ]
                                ]
                            ];
                            if(file_exists($this->getDataFolder().'floatingstats.yml')){
                                $fstats = yaml_parse_file($this->getDataFolder().'floatingstats.yml');
                                if(!in_array(strtolower($args[1]), $fstats)){
                                    $fstats[strtolower($args[1])] = $fstat;
                                    yaml_emit_file($this->getDataFolder().'floatingstats.yml', $fstats);
                                    $this->spawnFloatingStats($fstat['Name'], $sender);
                                    $sender->sendMessage(TF::GREEN.str_ireplace('{name}', $args[1], $this->getMessages('Player')['FloatingStatCreateSuccess']));
                                }else{
                                    $sender->sendMessage(TF::RED.str_ireplace('{name}', $args[1], $this->getMessages('Player')['FloatingStatExists']));
                                }
                            }else{
                                yaml_emit_file($this->getDataFolder().'floatingstats.yml', [strtolower($fstat['Name']) => $fstat]);
                                $this->spawnFloatingStats($fstat['Name'], $sender);
                                $sender->sendMessage(TF::GREEN.str_ireplace('{name}', $args[1], $this->getMessages('Player')['FloatingStatCreateSuccess']));
                            }
                            return true;
                        }elseif(count($args) < 2){
                            $sender->sendMessage(TF::RED.$this->getMessages('Player')['CommandErrorTooLessArguments']);
                            return false;
                        }else{
                            $sender->sendMessage(TF::RED.$this->getMessages('Player')['CommandErrorTooManyArguments']);
                            return false;
                        }
                  }else{
                      $sender->sendMessage(TF::RED.$this->getMessages('Player')['CommandErrorConsole']);
                      return true;
                  }
                }elseif($args[0] == 'list'){
                    if(file_exists($this->getDataFolder().'floatingstats.yml')){
                        $fstats = yaml_parse_file($this->getDataFolder().'floatingstats.yml');
                        if(count($fstats) > 0){
                            foreach($fstats as $fstat){
                                $sender->sendMessage(TF::GREEN.str_ireplace(['{name}', '{x}', '{y}', '{z}'], [$fstat['Name'], $fstat['Position']['X'], $fstat['Position']['Y'], $fstat['Position']['Z']], $this->getMessages('Player')['FloatingStatList']));
                            }
                        }else{
                            $sender->sendMessage(TF::RED.$this->getMessages('Player')['CommandErrorNoFloatingStats']);
                        }
                    }else{
                        $sender->sendMessage(TF::RED.$this->getMessages('Player')['CommandErrorNoFloatingStats']);
                    }
                    return true;
                }elseif($args[0] == 'remove'){
                    if(count($args) == 2){
                        if(file_exists($this->getDataFolder().'floatingstats.yml')){
                            $fstats = yaml_parse_file($this->getDataFolder().'floatingstats.yml');
                            if(count($fstats) > 0){
                                if(isset($fstats[strtolower($args[1])])){
                                    unset($fstats[strtolower($args[1])]);
                                    yaml_emit_file($this->getDataFolder().'floatingstats.yml', $fstats);
                                    $sender->sendMessage(TF::GREEN.str_ireplace('{name}', $args[1],$this->getMessages('Player')['FloatingStatRemoveSuccess']));
                                }else{
                                    $sender->sendMessage(TF::RED.str_ireplace('{name}', $args[1],$this->getMessages('Player')['FloatingStatNotExists']));
                                }
                            }else{
                                $sender->sendMessage(TF::RED.$this->getMessages('Player')['CommandErrorNoFloatingStats']);
                            }
                        }else{
                            $sender->sendMessage(TF::RED.$this->getMessages('Player')['CommandErrorNoFloatingStats']);
                        }
                        return true;
                    }elseif(count($args) < 2){
                        $sender->sendMessage(TF::RED.$this->getMessages('Player')['CommandErrorTooLessArguments']);
                        return false;
                    }else{
                        $sender->sendMessage(TF::RED.$this->getMessages('Player')['CommandErrorTooManyArguments']);
                        return false;
                    }
                }else{
                    $sender->sendMessage(TF::RED.$this->getMessages('Player')['CommandErrorTooLessArguments']);
                    return false;
                }
            }else{
                $sender->sendMessage(TF::RED.$this->getMessages('Player')['CommandErrorTooLessArguments']);
                return false;
            }
        }elseif($cmd == 'fixtable'){
            $sender->sendMessage(TF::GREEN.$this->getMessages('MySQL')['CommandFixTable']);
            $this->getServer()->getScheduler()->scheduleAsyncTask(new FixTableTask($this, $sender));
        }
    }

    public function saveData($player, $data, $stat = false){
        if(strtolower($this->getConfig()->get('Provider')) == 'json'){
            fwrite(fopen($this->getDataFolder().'Stats/'.strtolower($player->getName()).'.json', 'w'), json_encode($data, JSON_PRETTY_PRINT));
        }elseif(strtolower($this->getConfig()->get('Provider')) == 'mysql'){
            $this->getServer()->getScheduler()->scheduleAsyncTask($player, $this, $stat, $data);
        }
    }

    public function getStats($player, $type, $data){
        if($player instanceof Player){
            $player = $player->getName();
        }
        if(strtolower($type) == 'json'){
            if($data == 'all'){
                return json_decode(file_get_contents($this->getDataFolder().'Stats/'.strtolower($player).'.json'), true);
            }else{
                return json_decode(file_get_contents($this->getDataFolder().'Stats/'.strtolower($player).'.json'), true)[$data];
            }
        }
    }

    public function showStats($requestor, $target){
        if($target == 'CONSOLE'){
            $requestor->sendMessage(TF::RED.$this->getMessages('Player')['CommandErrorConsoleStats']);
        }else{
            if(strtolower($this->getConfig()->get('Provider')) == 'json'){
                if(file_exists($this->getDataFolder().'Stats/'.strtolower($target).'.json')){
                    $switch = $this->getConfig()->get('Stats');
                    $info = $this->getStats($target, 'json', 'all');
                    $requestor->sendMessage(TF::GOLD.str_ireplace('{value}', $info['PlayerName'], $this->getMessages('Player')['StatsFor']));
                    if($switch['Online']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['Online'], $this->getMessages('Player')['StatOnline']));
                    }
                    if($requestor->hasPermission('statspe.cmd.stats.advancedinfo')){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['ClientID'], $this->getMessages('Player')['StatClientID']));
                        @$requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['UUID'], $this->getMessages('Player')['StatUUID']));
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['XBoxAuthenticated'], $this->getMessages('Player')['StatXBoxAuthenticated']));
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['LastIP'], $this->getMessages('Player')['StatLastIP']));
                    }
                    if($switch['FirstJoin']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['FirstJoin'], $this->getMessages('Player')['StatFirstJoin']));
                    }
                    if($switch['LastJoin']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['LastJoin'], $this->getMessages('Player')['StatLastJoin']));
                    }
                    if($switch['JoinCount']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['JoinCount'], $this->getMessages('Player')['StatJoinCount']));
                    }
                    if($switch['KillCount']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['KillCount'], $this->getMessages('Player')['StatKillCount']));
                    }
                    if($switch['DeathCount']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['DeathCount'], $this->getMessages('Player')['StatDeathCount']));
                    }
                    if($info['DeathCount'] > 0 && $switch['K/D']){
                        $this->requestor->sendMessage(str_replace('{value}', $info['KillCount'] / $info['DeathCount'], $this->getMessages('Player')['K/D']));
                    }
                    if($switch['KickCount']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['KickCount'], $this->getMessages('Player')['StatKickCount']));
                    }
                    if($switch['OnlineTime']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['OnlineTime'], $this->getMessages('Player')['StatOnlineTime']));
                    }
                    if($switch['BlockBreakCount']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['BlocksBreaked'], $this->getMessages('Player')['StatBlockBreakCount']));
                    }
                    if($switch['BlockPlaceCount']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['BlocksPlaced'], $this->getMessages('Player')['StatBlockPlaceCount']));
                    }
                    if($switch['ChatCount']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['ChatMessages'], $this->getMessages('Player')['StatChatMessageCount']));
                    }
                    if($switch['FishCount']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['FishCount'], $this->getMessages('Player')['StatFishCount']));
                    }
                    if($switch['BedEnterCount']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['EnterBedCount'], $this->getMessages('Player')['StatBedEnterCount']));
                    }
                    if($switch['EatCount']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['EatCount'], $this->getMessages('Player')['StatEatCount']));
                    }
                    if($switch['CraftCount']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['CraftCount'], $this->getMessages('Player')['StatCraftCount']));
                    }
                    if($switch['DroppedItems']){
                        $requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $info['DroppedItems'], $this->getMessages('Player')['StatDroppedItems']));
                    }
                }else{
                    $requestor->sendMessage(TF::RED.str_ireplace('{value}', $target, $this->getMessages('Player')['CommandErrorNoStats']));
                }
            }elseif(strtolower($this->getConfig()->get('Provider')) == 'mysql'){
                $requestor->sendMessage(TF::GREEN.$this->getMessages('MySQL')['CommandRunning']);
                $this->getServer()->getScheduler()->scheduleAsyncTask(new ShowStatsTask($this, $requestor, $target));
            }
        }
    }

    public function spawnFloatingStats($stat = false, $player = false, $target = null){
        if(is_object($player)){
            $player = $player->getName();
        }
        if(file_exists($this->getDataFolder().'floatingstats.yml')){
            $fstats = yaml_parse_file($this->getDataFolder().'floatingstats.yml');
            if(!$stat){
                foreach($fstats as $fstat){
                    if($fstat['Enabled']){
                        if($fstat['PlayerName']){
                            $player = $fstat['PlayerName'];
                        }
                        if(strtolower($this->getConfig()->get('Provider')) == 'json'){
                            $info = $this->getStats($player, 'JSON', 'all');
                            $text['PlayerName'] = TF::GOLD.str_ireplace('{value}', $info['PlayerName'], $this->getMessages('Player')['StatsFor']);
                            foreach($fstat['Stats'] as $stat){
                                if($stat['Enabled']){
                                    if($stat['Name'] == 'K/D'){
                                        if($info['DeathCount'] > 0){
                                            $text['K/D'] = TF::AQUA.str_ireplace('{value}', $info['KillCount'] / $info['DeathCount'], $this->getMessages('Player')['StatK/D']);
                                        }
                                    }else{
                                        $text[$stat['Name']] = TF::AQUA.str_ireplace('{value}', $info[$stat['Name']], $this->getMessages('Player')[$stat['Lang']]);
                                    }
                                }
                            }
                            $text = implode("\n", $text);
                            if($this->getServer()->isLevelLoaded($fstat['Position']['Level'])){
                                $this->getServer()->getLevelByName($fstat['Position']['Level'])->addparticle(new FloatingTextParticle(new Vector3($fstat['Position']['X'], $fstat['Position']['Y'], $fstat['Position']['Z']), '', $text), [$target]);
                            }
                        }elseif(strtolower($this->getConfig()->get('Provider')) == 'mysql'){
                            $this->getServer()->getScheduler()->scheduleAsyncTask(new SpawnFloatStatTask($this, $fstat, $player, $target));
                        }
                    }
                }
            }else{
                $fstat = $fstats[strtolower($stat)];
                if($fstat['Enabled']){
                    if(strtolower($this->getConfig()->get('Provider')) == 'json'){
                        $info = $this->getStats($player, 'JSON', 'all');
                        $text['PlayerName'] = TF::GOLD.str_ireplace('{value}', $info['PlayerName'], $this->getMessages('Player')['StatsFor']);
                        foreach($fstat['Stats'] as $stat){
                            if($stat['Enabled']){
                                if($stat['Name'] == 'K/D'){
                                    if($info['DeathCount'] > 0){
                                        $text['K/D'] = TF::AQUA.str_ireplace('{value}', $info['KillCount'] / $info['DeathCount'], $this->getMessages('Player')['StatK/D']);
                                    }
                                }else{
                                    $text[$stat['Name']] = TF::AQUA.str_ireplace('{value}', $info[$stat['Name']], $this->getMessages('Player')[$stat['Lang']]);
                                }
                            }
                        }
                        $text = implode("\n", $text);
                        if($this->getServer()->isLevelLoaded($fstat['Position']['Level'])){
                            $this->getServer()->getLevelByName($fstat['Position']['Level'])->addparticle(new FloatingTextParticle(new Vector3($fstat['Position']['X'], $fstat['Position']['Y'], $fstat['Position']['Z']), '', $text), [$target]);
                        }
                    }elseif(strtolower($this->getConfig()->get('Provider')) == 'mysql'){
                        $this->getServer()->getScheduler()->scheduleAsyncTask(new SpawnFloatStatTask($this, $fstat, $player, $target));
                    }
                }
            }
        }
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $provider = strtolower($this->getConfig()->get('Provider'));
        //if($player->isXboxAuthenticated()){
        //    $xa = $this->getMessages('Player')['StatYes'];
        //}else{
              $xa = $this->getMessages('Player')['StatNo'];
        //}
        $pn = $player->getName();
        if($provider == 'json'){
            if(file_exists($this->getDataFolder().'/Stats/'.strtolower($player->getName()).'.json')){
                $info = $this->getStats($player->getName(), 'JSON', 'all');
                $cid = $player->getClientId();
                $ip = $player->getAddress();
                $ls = date($this->getConfig()->get('TimeFormat'));
                $jc = $info['JoinCount'] + 1;
                $data = array(
                      'PlayerName' => $pn,
                      'Online' => $this->getMessages('Player')['StatYes'],
                      'ClientID' => $cid,
                      'UUID' => $player->getUniqueId(),
                      'XBoxAuthenticated' => $xa,
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
                      'CraftCount' => $info['CraftCount'],
                );
                $this->saveData($player, $data);
            }else{
                $fp = date($this->getConfig()->get('TimeFormat'));
                $cid = $player->getClientId();
                $ip = $player->getAddress();
                $data = array(
                      'PlayerName' => $pn,
                      'Online' => $this->getMessages('Player')['StatYes'],
                      'ClientID' => $cid,
                      'UUID' => $player->getUniqueId(),
                      'XBoxAuthenticated' => $xa,
                      'LastIP' => $ip,
                      'FirstJoin' => $fp,
                      'LastJoin' => $fp,
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
                      'CraftCount' => '0',
                      'DroppedItems' => '0'
                );
                $this->saveData($player, $data);
            }
        }elseif($provider == 'mysql'){
            $stats = array(
                '1' => [
                    'Stat' => 'PlayerName',
                    'Type' => 'Normal',
                    'Data' => $player->getName()
                ],
                '2' => [
                    'Stat' => 'Online',
                    'Type' => 'Normal',
                    'Data' => $this->getMessages('Player')['StatYes']
                ],
                '3' => [
                    'Stat' => 'ClientID',
                    'Type' => 'Normal',
                    'Data' => $player->getClientId()
                ],
                '4' => [
                    'Stat' => 'UUID',
                    'Type' => 'Normal',
                    'Data' => $player->getUniqueId()
                ],
                '5' => [
                    'Stat' => 'XBoxAuthenticated',
                    'Type' => 'Normal',
                    'Data' => $xa
                ],
                '6' => [
                    'Stat' => 'LastIP',
                    'Type' => 'Normal',
                    'Data' => $player->getAddress()
                ],
                '7' => [
                    'Stat' => 'LastJoin',
                    'Type' => 'Normal',
                    'Data' => date($this->getConfig()->get('TimeFormat'))
                ],
                '8' => [
                    'Stat' => 'JoinCount',
                    'Type' => 'Count',
                    'Data' => 1
                ]
            );
            foreach($stats as $stat){
                $this->getServer()->getScheduler()->scheduleAsyncTask(new SaveDataTask($player, $this, $stat['Stat'], $stat['Type'], $stat['Data']));
            }
        }
        $this->spawnFloatingStats(false, $player, $player);
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $provider = strtolower($this->getConfig()->get('Provider'));
        if($provider == 'json'){
            $info = $this->getStats($player->getName(), 'JSON', 'all');
            $data = array(
                'PlayerName' => $info['PlayerName'],
                'Online' => $this->getMessages('Player')['StatNo'],
                'ClientID' => $info['ClientID'],
                'UUID' => $info['UUID'],
                'XBoxAuthenticated' => $info['XBoxAuthenticated'],
                'LastIP' => $info['LastIP'],
                'FirstJoin' => $info['FirstJoin'],
                'LastJoin' => $info['LastJoin'],
                'JoinCount' => $info['JoinCount'],
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
                'CraftCount' => $info['CraftCount'],
                'DroppedItems' => $info['DroppedItems']
            );
            $this->saveData($player, $data);
        }elseif($provider == 'mysql'){
            $this->getServer()->getScheduler()->scheduleAsyncTask(new SaveDataTask($player, $this, 'Online', 'Normal', $this->getMessages('Player')['StatYes']));
        }
    }

    public function onDeath(PlayerDeathEvent $event){
        $player = $event->getPlayer();
        $damagecause = $player->getLastDamageCause();
        $provider = strtolower($this->getConfig()->get('Provider'));
        if($provider == 'json'){
            $info = $this->getStats($player->getName(), 'JSON', 'all');
            $data = array(
                'PlayerName' => $info['PlayerName'],
                'Online' => $info['Online'],
                'ClientID' => $info['ClientID'],
                'UUID' => $info['UUID'],
                'XBoxAuthenticated' => $info['XBoxAuthenticated'],
                'LastIP' => $info['LastIP'],
                'FirstJoin' => $info['FirstJoin'],
                'LastJoin' => $info['LastJoin'],
                'JoinCount' => $info['JoinCount'],
                'KillCount' => $info['KillCount'],
                'DeathCount' => $info['DeathCount'] + 1,
                'KickCount' => $info['KickCount'],
                'OnlineTime' => $info['OnlineTime'],
                'BlocksBreaked' => $info['BlocksBreaked'],
                'BlocksPlaced' => $info['BlocksPlaced'],
                'ChatMessages' => $info['ChatMessages'],
                'FishCount' => $info['FishCount'],
                'EnterBedCount' => $info['EnterBedCount'],
                'EatCount' => $info['EatCount'],
                'CraftCount' => $info['CraftCount'],
                'DroppedItems' => $info['DroppedItems']
          );
          $this->saveData($player, $data);
          if(method_exists($damagecause, 'getDamager')){
              if($damagecause->getDamager() instanceof Player){
                  $killer = $damagecause->getDamager();
                  $kinfo = $this->getStats($killer->getName(), 'JSON', 'all');
                  $kdata = array(
                      'PlayerName' => $kinfo['PlayerName'],
                      'Online' => $kinfo['Online'],
                      'ClientID' => $kinfo['ClientID'],
                      'UUID' => $kinfo['UUID'],
                      'XBoxAuthenticated' => $kinfo['XBoxAuthenticated'],
                      'LastIP' => $kinfo['LastIP'],
                      'FirstJoin' => $kinfo['FirstJoin'],
                      'LastJoin' => $kinfo['LastJoin'],
                      'JoinCount' => $kinfo['JoinCount'],
                      'KillCount' => $kinfo['KillCount'] + 1,
                      'DeathCount' => $kinfo['DeathCount'],
                      'KickCount' => $kinfo['KickCount'],
                      'OnlineTime' => $kinfo['OnlineTime'],
                      'BlocksBreaked' => $kinfo['BlocksBreaked'],
                      'BlocksPlaced' => $kinfo['BlocksPlaced'],
                      'ChatMessages' => $kinfo['ChatMessages'],
                      'FishCount' => $kinfo['FishCount'],
                      'EnterBedCount' => $kinfo['EnterBedCount'],
                      'EatCount' => $kinfo['EatCount'],
                      'CraftCount' => $kinfo['CraftCount'],
                      'DroppedItems' => $kinfo['DroppedItems']
                  );
                  $this->saveData($killer, $kdata);
                }
            }
        }elseif($provider == 'mysql'){
            $this->getServer()->getScheduler()->scheduleAsyncTask(new SaveDataTask($player, $this, 'DeathCount', 'Count', '1'));
            if(method_exists($damagecause, 'getDamager')){
                if($damagecause->getDamager() instanceof Player){
                    $this->getServer()->getScheduler()->scheduleAsyncTask(new SaveDataTask($damagecause->getDamager(), $this, 'KillCount', 'Count', '1'));
                }
            }
        }
    }

    public function onKick(PlayerKickEvent $event){
        $player = $event->getPlayer();
        $provider = strtolower($this->getConfig()->get('Provider'));
        if($provider == 'JSON'){
            $info = $this->getStats($player->getName(), 'JSON', 'all');
            $data = array(
                'PlayerName' => $info['PlayerName'],
                'Online' => $info['Online'],
                'ClientID' => $info['ClientID'],
                'UUID' => $info['UUID'],
                'XBoxAuthenticated' => $info['XBoxAuthenticated'],
                'LastIP' => $info['LastIP'],
                'FirstJoin' => $info['FirstJoin'],
                'LastJoin' => $info['LastJoin'],
                'JoinCount' => $info['JoinCount'],
                'KillCount' => $info['KillCount'],
                'DeathCount' => $info['DeathCount'],
                'KickCount' => $info['KickCount'] + 1,
                'OnlineTime' => $info['OnlineTime'],
                'BlocksBreaked' => $info['BlocksBreaked'],
                'BlocksPlaced' => $info['BlocksPlaced'],
                'ChatMessages' => $info['ChatMessages'],
                'FishCount' => $info['FishCount'],
                'EnterBedCount' => $info['EnterBedCount'],
                'EatCount' => $info['EatCount'],
                'CraftCount' => $info['CraftCount'],
                'DroppedItems' => $info['DroppedItems']
          );
            $this->saveData($player, $data);
        }elseif($provider == 'mysql'){
            $this->getServer()->getScheduler()->scheduleAsyncTask(new SaveDataTask($player, $this, 'KickCount', 'Count', '1'));
        }
    }

    public function onBlockBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();
        $provider = strtolower($this->getConfig()->get('Provider'));
        if($provider == 'json'){
            $info = $this->getStats($player->getName(), 'JSON', 'all');
            $data = array(
                'PlayerName' => $info['PlayerName'],
                'Online' => $info['Online'],
                'ClientID' => $info['ClientID'],
                'UUID' => $info['UUID'],
                'XBoxAuthenticated' => $info['XBoxAuthenticated'],
                'LastIP' => $info['LastIP'],
                'FirstJoin' => $info['FirstJoin'],
                'LastJoin' => $info['LastJoin'],
                'JoinCount' => $info['JoinCount'],
                'KillCount' => $info['KillCount'],
                'DeathCount' => $info['DeathCount'],
                'KickCount' => $info['KickCount'],
                'OnlineTime' => $info['OnlineTime'],
                'BlocksBreaked' => $info['BlocksBreaked'] + 1,
                'BlocksPlaced' => $info['BlocksPlaced'],
                'ChatMessages' => $info['ChatMessages'],
                'FishCount' => $info['FishCount'],
                'EnterBedCount' => $info['EnterBedCount'],
                'EatCount' => $info['EatCount'],
                'CraftCount' => $info['CraftCount'],
                'DroppedItems' => $info['DroppedItems']
          );
            $this->saveData($player, $data);
        }elseif($provider == 'mysql'){
            $this->getServer()->getScheduler()->scheduleAsyncTask(new SaveDataTask($player, $this, 'BlocksBreaked', 'Count', '1'));
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event){
        $player = $event->getPlayer();
        $provider = strtolower($this->getConfig()->get('Provider'));
        if($provider == 'json'){
            $info = $this->getStats($player->getName(), 'JSON', 'all');
            $data = array(
                 'PlayerName' => $info['PlayerName'],
                 'Online' => $info['Online'],
                 'ClientID' => $info['ClientID'],
                 'UUID' => $info['UUID'],
                 'XBoxAuthenticated' => $info['XBoxAuthenticated'],
                 'LastIP' => $info['LastIP'],
                 'FirstJoin' => $info['FirstJoin'],
                 'LastJoin' => $info['LastJoin'],
                 'JoinCount' => $info['JoinCount'],
                 'KillCount' => $info['KillCount'],
                 'DeathCount' => $info['DeathCount'],
                 'KickCount' => $info['KickCount'],
                 'OnlineTime' => $info['OnlineTime'],
                 'BlocksBreaked' => $info['BlocksBreaked'],
                 'BlocksPlaced' => $info['BlocksPlaced'] + 1,
                 'ChatMessages' => $info['ChatMessages'],
                 'FishCount' => $info['FishCount'],
                 'EnterBedCount' => $info['EnterBedCount'],
                 'EatCount' => $info['EatCount'],
                 'CraftCount' => $info['CraftCount'],
                 'DroppedItems' => $info['DroppedItems']
            );
            $this->saveData($player, $data);
        }elseif($provider == 'mysql'){
            $this->getServer()->getScheduler()->scheduleAsyncTask(new SaveDataTask($player, $this, 'BlocksPlaced', 'Count', '1'));
        }
    }

    public function onChat(PlayerChatEvent $event){
        $player = $event->getPlayer();
        $provider = strtolower($this->getConfig()->get('Provider'));
        if($provider == 'json'){
            $info = $this->getStats($player->getName(), 'JSON', 'all');
            $data = array(
                  'PlayerName' => $info['PlayerName'],
                  'Online' => $info['Online'],
                  'ClientID' => $info['ClientID'],
                  'UUID' => $info['UUID'],
                  'XBoxAuthenticated' => $info['XBoxAuthenticated'],
                  'LastIP' => $info['LastIP'],
                  'FirstJoin' => $info['FirstJoin'],
                  'LastJoin' => $info['LastJoin'],
                  'JoinCount' => $info['JoinCount'],
                  'KillCount' => $info['KillCount'],
                  'DeathCount' => $info['DeathCount'],
                  'KickCount' => $info['KickCount'],
                  'OnlineTime' => $info['OnlineTime'],
                  'BlocksBreaked' => $info['BlocksBreaked'],
                  'BlocksPlaced' => $info['BlocksPlaced'],
                  'ChatMessages' => $info['ChatMessages'] + 1,
                  'FishCount' => $info['FishCount'],
                  'EnterBedCount' => $info['EnterBedCount'],
                  'EatCount' => $info['EatCount'],
                  'CraftCount' => $info['CraftCount'],
                  'DroppedItems' => $info['DroppedItems']
            );
            $this->saveData($player, $data);
        }elseif($provider == 'mysql'){
            $this->getServer()->getScheduler()->scheduleAsyncTask(new SaveDataTask($player, $this, 'ChatMessages', 'Count', '1'));
        }
    }

    public function onFish(PlayerFishEvent $event){
        $player = $event->getPlayer();
        $provider = strtolower($this->getConfig()->get('Provider'));
        if($provider == 'json'){
            $info = $this->getStats($player->getName(), 'JSON', 'all');
            $data = array(
                'PlayerName' => $info['PlayerName'],
                'Online' => $info['Online'],
                'ClientID' => $info['ClientID'],
                'UUID' => $info['UUID'],
                'XBoxAuthenticated' => $info['XBoxAuthenticated'],
                'LastIP' => $info['LastIP'],
                'FirstJoin' => $info['FirstJoin'],
                'LastJoin' => $info['LastJoin'],
                'JoinCount' => $info['JoinCount'],
                'KillCount' => $info['KillCount'],
                'DeathCount' => $info['DeathCount'],
                'KickCount' => $info['KickCount'],
                'OnlineTime' => $info['OnlineTime'],
                'BlocksBreaked' => $info['BlocksBreaked'],
                'BlocksPlaced' => $info['BlocksPlaced'],
                'ChatMessages' => $info['ChatMessages'],
                'FishCount' => $info['FishCount'] + 1,
                'EnterBedCount' => $info['EnterBedCount'],
                'EatCount' => $info['EatCount'],
                'CraftCount' => $info['CraftCount'],
                'DroppedItems' => $info['DroppedItems']
            );
            $this->saveData($player, $data);
        }elseif($provider == 'mysql'){
            $this->getServer()->getScheduler()->scheduleAsyncTask(new SaveDataTask($player, $this, 'FishCount', 'Count', '1'));
        }
    }

    public function onBedEnter(PlayerBedEnterEvent $event){
        $player = $event->getPlayer();
        $provider = strtolower($this->getConfig()->get('Provider'));
        if($provider == 'json'){
            $info = $this->getStats($player->getName(), 'JSON', 'all');
            $data = array(
                'PlayerName' => $info['PlayerName'],
                'Online' => $info['Online'],
                'ClientID' => $info['ClientID'],
                'UUID' => $info['UUID'],
                'XBoxAuthenticated' => $info['XBoxAuthenticated'],
                'LastIP' => $info['LastIP'],
                'FirstJoin' => $info['FirstJoin'],
                'LastJoin' => $info['LastJoin'],
                'JoinCount' => $info['JoinCount'],
                'KillCount' => $info['KillCount'],
                'DeathCount' => $info['DeathCount'],
                'KickCount' => $info['KickCount'],
                'OnlineTime' => $info['OnlineTime'],
                'BlocksBreaked' => $info['BlocksBreaked'],
                'BlocksPlaced' => $info['BlocksPlaced'],
                'ChatMessages' => $info['ChatMessages'],
                'FishCount' => $info['FishCount'],
                'EnterBedCount' => $info['EnterBedCount'] + 1,
                'EatCount' => $info['EatCount'],
                'CraftCount' => $info['CraftCount'],
                'DroppedItems' => $info['DroppedItems']
            );
            $this->saveData($player, $data);
        }elseif($provider == 'mysql'){
            $this->getServer()->getScheduler()->scheduleAsyncTask(new SaveDataTask($player, $this, 'EnterBedCount', 'Count', '1'));
        }
    }

    public function onConsumeItem(PlayerItemConsumeEvent $event){
        $player = $event->getPlayer();
        $provider = strtolower($this->getConfig()->get('Provider'));
        if($provider == 'json'){
            $info = $this->getStats($player->getName(), 'JSON', 'all');
            $data = array(
                'PlayerName' => $info['PlayerName'],
                'Online' => $info['Online'],
                'ClientID' => $info['ClientID'],
                'UUID' => $info['UUID'],
                'XBoxAuthenticated' => $info['XBoxAuthenticated'],
                'LastIP' => $info['LastIP'],
                'FirstJoin' => $info['FirstJoin'],
                'LastJoin' => $info['LastJoin'],
                'JoinCount' => $info['JoinCount'],
                'KillCount' => $info['KillCount'],
                'DeathCount' => $info['DeathCount'],
                'KickCount' => $info['KickCount'],
                'OnlineTime' => $info['OnlineTime'],
                'BlocksBreaked' => $info['BlocksBreaked'],
                'BlocksPlaced' => $info['BlocksPlaced'],
                'ChatMessages' => $info['ChatMessages'],
                'FishCount' => $info['FishCount'],
                'EnterBedCount' => $info['EnterBedCount'],
                'EatCount' => $info['EatCount'] + 1,
                'CraftCount' => $info['CraftCount'],
                'DroppedItems' => $info['DroppedItems']
            );
            $this->saveData($player, $data);
        }elseif($provider == 'mysql'){
            $this->getServer()->getScheduler()->scheduleAsyncTask(new SaveDataTask($player, $this, 'EatCount', 'Count', '1'));
        }
    }

    public function onCraft(CraftItemEvent $event){
        $player = $event->getPlayer();
        $provider = strtolower($this->getConfig()->get('Provider'));
        if($provider == 'json'){
            $info = $this->getStats($player->getName(), 'JSON', 'all');
            $data = array(
                'PlayerName' => $info['PlayerName'],
                'Online' => $info['Online'],
                'ClientID' => $info['ClientID'],
                'UUID' => $info['UUID'],
                'XBoxAuthenticated' => $info['XBoxAuthenticated'],
                'LastIP' => $info['LastIP'],
                'FirstJoin' => $info['FirstJoin'],
                'LastJoin' => $info['LastJoin'],
                'JoinCount' => $info['JoinCount'],
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
                'CraftCount' => $info['CraftCount'] + 1,
                'DroppedItems' => $info['DroppedItems']
            );
            $this->saveData($player, $data);
        }elseif($provider == 'mysql'){
            $this->getServer()->getScheduler()->scheduleAsyncTask(new SaveDataTask($player, $this, 'CraftCount', 'Count', '1'));
        }
    }

    public function onItemDrop(PlayerDropItemEvent $event){
        $player = $event->getPlayer();
        $provider = strtolower($this->getConfig()->get('Provider'));
        if($provider == 'json'){
            $info = $this->getStats($player->getName(), 'JSON', 'all');
            $data = array(
                'PlayerName' => $info['PlayerName'],
                'Online' => $info['Online'],
                'ClientID' => $info['ClientID'],
                'UUID' => $info['UUID'],
                'XBoxAuthenticated' => $info['XBoxAuthenticated'],
                'LastIP' => $info['LastIP'],
                'FirstJoin' => $info['FirstJoin'],
                'LastJoin' => $info['LastJoin'],
                'JoinCount' => $info['JoinCount'],
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
                'CraftCount' => $info['CraftCount'],
                'DroppedItems' => $info['DroppedItems'] + 1
            );
            $this->saveData($player, $data);
        }elseif($provider == 'mysql'){
            $this->getServer()->getScheduler()->scheduleAsyncTask(new SaveDataTask($player, $this, 'DroppedItems', 'Count', 1));
        }
    }

    public function update($nversion){
        $url = Utils::getURL($this->getDescription()->getWebsite().'MCPE-Plugins/Updater/Updater.php?plugin='.$this->getDescription()->getName().'&type=downloadurl');
        $md5 = Utils::getURL($this->getDescription()->getWebsite().'MCPE-Plugins/Updater/Updater.php?plugin='.$this->getDescription()->getName().'&type=md5');
        $this->getServer()->getScheduler()->scheduleTask(new UpdaterTask($url, $md5, $this->getDescription()->getVersion(), $nversion, $this));
    }
}
