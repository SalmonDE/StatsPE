<?php
namespace StatsPE\Tasks;

use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class SaveDataTask extends AsyncTask
{
  public function __construct($player, $owner, $stat, $data){
      $this->mysql = $owner->getConfig()->get('MySQL');
      $this->stat = $stat;
      $this->data = $data;
      $this->timeformat = $owner->getConfig()->get('TimeFormat');
      if($player instanceof Player){
          $this->player = strtolower($player->getName());
      }else{
          $this->player = strtolower($player);
      }
  }

  public function onRun(){
      $connection = mysqli_connect($this->mysql['host'], $this->mysql['user'], $this->mysql['password'], $this->mysql['database']);
      if($connection){
          $poccurence = mysqli_num_rows(mysqli_query($connection, "SELECT * FROM Stats WHERE PlayerName = '$this->player'"));
          $date = date($this->timeformat);
          if($poccurence == 1){
              mysqli_query($connection, "UPDATE Stats SET $this->stat = '".$this->data."' WHERE PlayerName = '".$this->player."'");
          }elseif($poccurence > 1){
              $this->setResult('Player '.$this->player.' is occuring more than once in Table Stats of the Database '.$this->mysql['database']);
          }else{
              mysqli_query($connection, "INSERT INTO Stats (PlayerName, XBoxAuthenticated, FirstJoin, LastJoin, JoinCount, KillCount, DeathCount, KickCount, OnlineTime, BlocksBreaked, BlocksPlaced, ChatMessages, FishCount, EnterBedCount, EatCount, CraftCount) VALUES ('$this->player', 'false', '$date', '$date', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)");
          }
          if(mysqli_error($connection)){
              $this->setResult(mysqli_error($connection));
          }else{
              $this->setResult(false);
          }
      }else{
              $this->setResult('Could not connect to Database');
      }
  }

  public function onCompletion(Server $server){
      if($this->getResult()){
          $server->getPluginManager()->getPlugin('StatsPE')->getLogger()->error('Error while Saving Data of '.$this->player.': '.$this->getResult());
      }
  }
}
