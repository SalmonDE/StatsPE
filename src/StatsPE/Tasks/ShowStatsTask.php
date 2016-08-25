<?php
namespace StatsPE\Tasks;

use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;

class ShowStatsTask extends AsyncTask
{
  public function __construct($owner, $requestor, $target){
      $this->target = strtolower($target);
      if($requestor->getName() == 'CONSOLE'){
          $this->requestor = $requestor;
      }elseif($requestor instanceof Player){
          $this->requestor = $requestor->getName();
      }else{
          $this->cancelRun();
      }
      $this->mysql = $owner->getConfig()->get('MySQL');
      $this->lang = $owner->getMessages();
  }

  public function onRun(){
      $connection = @mysqli_connect($this->mysql['host'], $this->mysql['user'], $this->mysql['password'], $this->mysql['database']);
      if($connection){
          $data = mysqli_query($connection, "SELECT * FROM Stats WHERE PlayerName = '$this->target'");
          if(mysqli_num_rows($data) == 1){
              $this->setResult(mysqli_fetch_assoc($data));
          }elseif(mysqli_num_rows($data) > 1){
              $this->setResult(TF::RED.str_ireplace('{value}', $this->target, $lang['Player']['CommandMultipleOccurences']));
          }else{
              $this->setResult(TF::RED.str_ireplace('{value}', $this->target, $lang['Player']['CommandErrorNoStats']));
          }
      }else{
          $this->setResult(TF::RED.$lang['MySQL']['CommandConnectionFailed']);
      }
  }

  public function onCompletion(Server $server){
      if(is_array($this->getResult())){
          $data = $this->getResult();
          if(is_string($this->requestor)){
              $server->getPlayerExact($this->requestor)->sendMessage(TF::GOLD.'---Statistics for: '.TF::GREEN.$data['PlayerName'].TF::GOLD.'---');
              if($server->getPlayerExact($this->requestor)->hasPermission('statspe.cmd.stats.advancedinfo')){
                  $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Last ClientID: '.TF::LIGHT_PURPLE.$data['ClientID']);
                  $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Last UUID: '.TF::LIGHT_PURPLE.$data['UUID']);
                  $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'XBoxAuthenticated: '.TF::LIGHT_PURPLE.$data['XBoxAuthenticated']);
                  $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Last IP: '.TF::LIGHT_PURPLE.$data['LastIP']);
              }
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'First Join: '.TF::LIGHT_PURPLE.$data['FirstJoin']);
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Last Join: '.TF::LIGHT_PURPLE.$data['LastJoin']);
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Total Joins: '.TF::LIGHT_PURPLE.$data['JoinCount']);
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Kills: '.TF::LIGHT_PURPLE.$data['KillCount']);
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Deaths: '.TF::LIGHT_PURPLE.$data['DeathCount']);
              if(!$data['DeathCount'] == 0){
                  $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'K/D: '.TF::LIGHT_PURPLE.$data['KillCount'] / $data['DeathCount']);
              }
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Kicks: '.TF::LIGHT_PURPLE.$data['KickCount']);
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Online Time: '.TF::LIGHT_PURPLE.$data['OnlineTime']);
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Breaked Blocks: '.TF::LIGHT_PURPLE.$data['BlocksBreaked']);
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Placed Blocks: '.TF::LIGHT_PURPLE.$data['BlocksPlaced']);
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Chat Messages: '.TF::LIGHT_PURPLE.$data['ChatMessages']);
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Catched Fishes: '.TF::LIGHT_PURPLE.$data['FishCount']);
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Went to bed for: '.TF::LIGHT_PURPLE.$data['EnterBedCount'].TF::AQUA.' times');
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Ate something for: '.TF::LIGHT_PURPLE.$data['EatCount'].TF::AQUA.' times');
              $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Crafted something for: '.TF::LIGHT_PURPLE.$data['CraftCount'].TF::AQUA.' times');
          }elseif($this->requestor->getName() === 'CONSOLE'){
            $this->requestor->sendMessage(TF::GOLD.'---Statistics for: '.TF::GREEN.$data['PlayerName'].TF::GOLD.'---');
            $this->requestor->sendMessage(TF::AQUA.'Last ClientID: '.TF::LIGHT_PURPLE.$data['ClientID']);
            $this->requestor->sendMessage(TF::AQUA.'Last UUID: '.TF::LIGHT_PURPLE.$data['UUID']);
            $this->requestor->sendMessage(TF::AQUA.'XBoxAuthenticated: '.TF::LIGHT_PURPLE.$data['XBoxAuthenticated']);
            $this->requestor->sendMessage(TF::AQUA.'Last IP: '.TF::LIGHT_PURPLE.$data['LastIP']);
            $this->requestor->sendMessage(TF::AQUA.'First Join: '.TF::LIGHT_PURPLE.$data['FirstJoin']);
            $this->requestor->sendMessage(TF::AQUA.'Last Join: '.TF::LIGHT_PURPLE.$data['LastJoin']);
            $this->requestor->sendMessage(TF::AQUA.'Total Joins: '.TF::LIGHT_PURPLE.$data['JoinCount']);
            $this->requestor->sendMessage(TF::AQUA.'Kills: '.TF::LIGHT_PURPLE.$data['KillCount']);
            $this->requestor->sendMessage(TF::AQUA.'Deaths: '.TF::LIGHT_PURPLE.$data['DeathCount']);
            if(!$data['DeathCount'] == 0){
                $this->requestor->sendMessage(TF::AQUA.'K/D: '.TF::LIGHT_PURPLE.$data['KillCount'] / $data['DeathCount']);
            }
            $this->requestor->sendMessage(TF::AQUA.'Kicks: '.TF::LIGHT_PURPLE.$data['KickCount']);
            $this->requestor->sendMessage(TF::AQUA.'Online Time: '.TF::LIGHT_PURPLE.$data['OnlineTime']);
            $this->requestor->sendMessage(TF::AQUA.'Breaked Blocks: '.TF::LIGHT_PURPLE.$data['BlocksBreaked']);
            $this->requestor->sendMessage(TF::AQUA.'Placed Blocks: '.TF::LIGHT_PURPLE.$data['BlocksPlaced']);
            $this->requestor->sendMessage(TF::AQUA.'Chat Messages: '.TF::LIGHT_PURPLE.$data['ChatMessages']);
            $this->requestor->sendMessage(TF::AQUA.'Catched Fishes: '.TF::LIGHT_PURPLE.$data['FishCount']);
            $this->requestor->sendMessage(TF::AQUA.'Went to bed for: '.TF::LIGHT_PURPLE.$data['EnterBedCount'].TF::AQUA.' times');
            $this->requestor->sendMessage(TF::AQUA.'Ate something for: '.TF::LIGHT_PURPLE.$data['EatCount'].TF::AQUA.' times');
            $this->requestor->sendMessage(TF::AQUA.'Crafted something for: '.TF::LIGHT_PURPLE.$data['CraftCount'].TF::AQUA.' times');
          }
      }else{
          if(is_string($this->requestor)){
              $server->getPlayerExact($this->requestor)->sendMessage($this->getResult());
          }else{
              $this->requestor->sendMessage($this->getResult());
          }
      }
  }
}
