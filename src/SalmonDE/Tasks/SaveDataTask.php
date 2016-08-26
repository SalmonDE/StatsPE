<?php
namespace SalmonDE\Tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class SaveDataTask extends AsyncTask
{

  public function __construct($player, $owner, $stat, $type, $data){
      $this->mysql = $owner->getConfig()->get('MySQL');
      $this->stat = $stat;
      $this->type = $type;
      $this->data = $data;
      $this->lang = $owner->getMessages();
      $this->timeformat = $owner->getConfig()->get('TimeFormat');
      $this->yes = $owner->getMessages('Player')['StatYes'];
      $this->no = $owner->getMessages('Player')['StatNo'];;
      if(is_object($player)){
          $this->player = strtolower($player->getName());
      }else{
          $this->player = strtolower($player);
      }
  }

  public function onRun(){
      $connection = @mysqli_connect($this->mysql['host'], $this->mysql['user'], $this->mysql['password'], $this->mysql['database']);
      if($connection){
          $poccurence = mysqli_num_rows(mysqli_query($connection, "SELECT * FROM Stats WHERE PlayerName = '$this->player'"));
          $date = date($this->timeformat);
          if($poccurence == 1){
              if($this->type == 'Count'){
                  $data = mysqli_query($connection, "SELECT * FROM Stats WHERE PlayerName = '$this->player'");
                  $data = mysqli_fetch_assoc($data);
                  $this->data = $this->data + $data[$this->stat];
              }
              mysqli_query($connection, "UPDATE Stats SET $this->stat = '$this->data' WHERE PlayerName = '$this->player'");
          }elseif($poccurence > 1){
              $this->setResult(str_ireplace('{value}', $this->player, $this->lang['Player']['CommandMultipleOccurences']));
          }else{
              mysqli_query($connection, "INSERT INTO Stats (PlayerName, Online, XBoxAuthenticated, FirstJoin, LastJoin, JoinCount, KillCount, DeathCount, KickCount, OnlineTime, BlocksBreaked, BlocksPlaced, ChatMessages, FishCount, EnterBedCount, EatCount, CraftCount) VALUES ('$this->player', '$this->yes', '$this->no', '$date', '$date', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)");
              mysqli_query($connection, "UPDATE Stats SET $this->stat = '$this->data' WHERE PlayerName = '$this->player'");
          }
          if(mysqli_error($connection)){
              $this->setResult(mysqli_error($connection));
          }else{
              $this->setResult(false);
          }
      }else{
              $this->setResult($this->lang['MySQL']['ConnectFailure']);
      }
  }

  public function onCompletion(Server $server){
      if($this->getResult()){
          $server->getPluginManager()->getPlugin('StatsPE')->getLogger()->error(str_ireplace(['{player}', '{error}'], [$this->player, $this->getResult()], $this->lang['MySQL']['DataSavingError']));
      }
  }
}
