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
      if($requestor instanceof Player){
          $this->requestor = $requestor->getName();
      }else{
          $this->requestor = $requestor;
      }
      $this->mysql = $owner->getConfig()->get('MySQL');
  }

  public function onRun(){
      $connection = @mysqli_connect($this->mysql['host'], $this->mysql['user'], $this->mysql['password'], $this->mysql['database']);
      if($connection){
          $data = mysqli_query($connection, "SELECT * FROM Stats WHERE PlayerName = '$this->target'");
          if(mysqli_num_rows($data) == 1){
              $this->setResult(mysqli_fetch_assoc($data));
          }elseif(mysqli_num_rows($data) > 1){
              $this->setResult(TF::RED.'Player: '.$this->target.' is occuring more than once in the database! Please report this to an administrator.');
          }else{
              $this->setResult(TF::RED.'No Stats found for: '.TF::GOLD.$this->target."\n".TF::RED.'Please check your spelling.');
          }
      }else{
          $this->setResult(TF::RED.'Connecting to Database failed! Please contact an administrator.');
      }
  }

  public function onCompletion(Server $server){
      if(is_array($this->getResult())){
          $data = $this->getResult();
          if(!is_object($this->requestor)){
              $this->requestor = $server->getPlayerExact($this->player);
          }
          $this->requestor->sendMessage(TF::GOLD.'---Statistics for: '.TF::GREEN.$data['PlayerName'].TF::GOLD.'---');
          if($this->requestor->hasPermission('statspe.cmd.stats.advancedinfo')){
              $this->requestor->sendMessage(TF::AQUA.'Last ClientID: '.TF::LIGHT_PURPLE.$data['ClientID']);
              $this->requestor->sendMessage(TF::AQUA.'Last UUID: '.TF::LIGHT_PURPLE.$data['UUID']);
              $this->requestor->sendMessage(TF::AQUA.'XBoxAuthenticated: '.TF::LIGHT_PURPLE.$data['XBoxAuthenticated']);
              $this->requestor->sendMessage(TF::AQUA.'Last IP: '.TF::LIGHT_PURPLE.$data['LastIP']);
          }
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
      }else{
          $this->requestor->sendMessage($this->getResult());
      }
  }
}
