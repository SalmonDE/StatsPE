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
          mysqli_query($connection, "
          IF EXISTS (
   SELECT
      *
   FROM
      Stats
   WHERE
      PlayerName=$this->player
) BEGIN
UPDATE
   Stats
SET
   $this->stat=$this->data
WHERE
   PlayerName=$this->player
END
ELSE BEGIN
   INSERT
   INTO
      Stats
      (PlayerName, XBoxAuthenticated, FirstJoin, LastJoin, JoinCount, KillCount, DeathCount, KickCount, OnlineTime, BlocksBreaked, BlocksPlaced, ChatMessages, FishCount, EnterBedCount, EatCount, CraftCount)
   VALUES
      ($this->player, 'false', date($this->timeformat), date($this->timeformat), 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)
END");
          if(mysqli_error($connection)){
              $this->setResult(mysqli_error($connection));
          }else{
              $this->setResult(false);
          }
    }
  }

  public function onCompletion(Server $server){
      if($this->getResult()){
          $server->getPluginManager()->getPlugin('StatsPE')->getLogger()->error('Error while Saving Data of '.$this->player.': '.$this->getResult());
      }
  }
}
