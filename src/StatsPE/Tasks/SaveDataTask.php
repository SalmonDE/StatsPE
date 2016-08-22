<?php
namespace StatsPE\Tasks;

use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;

class SaveDataTask extends AsyncTask
{
  public function __construct($player, $owner, $stat, $data){
      $this->mysql = $owner->getConfig()->get('MySQL');
      $this->stat = $stat;
      $this->data = $data;
      $this->timeformat = $owner->getConfig()->get('TimeFormat')
      if($player instanceof Player){
          $this->player = strtolower($player->getName());
      }else{
          $this->player = strtolower($player);
      }
  }

  public function onRun(){
      $connection = mysqli_connect($this->mysql['host'], $this->mysql['user'], $this->mysql['password']);
      if(!mysqli_query($connection, "EXISTS(SELECT Stats FROM $this->mysql['database']) WHERE PlayerName = $this->player")){
          mysqli_query($connection, "INSERT INTO Stats (PlayerName, XBoxAuthenticated, FirstJoin, LastJoin, JoinCount, KillCount, DeathCount, KickCount, OnlineTime, BlocksBreaked, BlocksPlaced, ChatMessages, FishCount, EnterBedCount, EatCount, CraftCount) VALUES ($this->player, 'false', date($this->timeformat), date($this->timeformat), 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)");
      }
      mysqli_query($connection, "UPDATE $this->mysql['database'] SET $this->stat = $this->data WHERE PlayerName = $this->player");
  }
}
