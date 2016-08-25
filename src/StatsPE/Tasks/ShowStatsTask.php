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
              $this->setResult(TF::RED.str_ireplace('{value}', $this->target, $this->lang['Player']['CommandMultipleOccurences']));
          }else{
              $this->setResult(TF::RED.str_ireplace('{value}', $this->target, $this->lang['Player']['CommandErrorNoStats']));
          }
      }else{
          $this->setResult(TF::RED.$this->lang['MySQL']['CommandConnectionFailed']);
      }
  }

  public function onCompletion(Server $server){
      if(is_array($this->getResult())){
          $data = $this->getResult();
          if(is_string($this->requestor)){
            $server->getPlayerExact($this->requestor)->sendMessage(TF::GOLD.str_ireplace('{value}', $data['PlayerName'], $this->lang['Player']['StatsFor']));
            if($server->getPlayerExact($this->requestor)->hasPermission('statspe.cmd.stats.advancedinfo')){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['ClientID'], $this->lang['Player']['StatClientID']));
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['UUID'], $this->lang['Player']['StatUUID']));
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['XBoxAuthenticated'], $this->lang['Player']['StatXBoxAuthenticated']));
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['LastIP'], $this->lang['Player']['StatLastIP']));
            }
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['FirstJoin'], $this->lang['Player']['StatFirstJoin']));
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['LastJoin'], $this->lang['Player']['StatLastJoin']));
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['JoinCount'], $this->lang['Player']['StatJoinCount']));
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['KillCount'], $this->lang['Player']['StatKillCount']));
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.'Deaths: '.TF::LIGHT_PURPLE.$data['DeathCount']);
            if(!$data['DeathCount'] == 0){
                $this->requestor->sendMessage(str_replace('{value}', $data['KillCount'] / $data['DeathCount'], $this->lang['Player']['K/D']));
            }
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['KickCount'], $this->lang['Player']['StatKickCount']));
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['OnlineTime'], $this->lang['Player']['StatOnlineTime']));
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['BlocksBreaked'], $this->lang['Player']['StatBlocksBreakCount']));
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['BlocksPlaced'], $this->lang['Player']['StatBlocksPlaceCount']));
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['ChatMessages'], $this->lang['Player']['StatChatMessageCount']));
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['FishCount'], $this->lang['Player']['StatFishCount']));
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['EnterBedCount'], $this->lang['Player']['StatBedEnterCount']));
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['EatCount'], $this->lang['Player']['StatEatCount']));
            $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['CraftCount'], $this->lang['Player']['StatCraftCount']));
          }elseif(method_exists($this->requestor, 'getName')){
            $this->requestor->sendMessage(TF::GOLD.str_ireplace('{value}', $data['PlayerName'], $this->lang['Player']['StatsFor']));
            if($this->requestor->hasPermission('statspe.cmd.stats.advancedinfo')){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['ClientID'], $this->lang['Player']['StatClientID']));
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['UUID'], $this->lang['Player']['StatUUID']));
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['XBoxAuthenticated'], $this->lang['Player']['StatXBoxAuthenticated']));
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['LastIP'], $this->lang['Player']['StatLastIP']));
            }
            $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['FirstJoin'], $this->lang['Player']['StatFirstJoin']));
            $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['LastJoin'], $this->lang['Player']['StatLastJoin']));
            $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['JoinCount'], $this->lang['Player']['StatJoinCount']));
            $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['KillCount'], $this->lang['Player']['StatKillCount']));
            $this->requestor->sendMessage(TF::AQUA.'Deaths: '.TF::LIGHT_PURPLE.$data['DeathCount']);
            if(!$data['DeathCount'] == 0){
                $this->requestor->sendMessage(str_replace('{value}', $data['KillCount'] / $data['DeathCount'], $this->lang['Player']['K/D']));
            }
            $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['KickCount'], $this->lang['Player']['StatKickCount']));
            $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['OnlineTime'], $this->lang['Player']['StatOnlineTime']));
            $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['BlocksBreaked'], $this->lang['Player']['StatBlocksBreakedCount']));
            $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['BlocksPlaced'], $this->lang['Player']['StatBlocksPlacedCount']));
            $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['ChatMessages'], $this->lang['Player']['StatChatMessageCount']));
            $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['FishCount'], $this->lang['Player']['StatFishCount']));
            $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['EnterBedCount'], $this->lang['Player']['StatBedEnterCount']));
            $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['EatCount'], $this->lang['Player']['StatEatCount']));
            $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['CraftCount'], $this->lang['Player']['StatCraftCount']));
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
