<?php
namespace SalmonDE\Tasks;

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
      $this->switch = $owner->getConfig()->get('Stats');
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
            if($this->switch['Online']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['Online'], $this->lang['Player']['StatOnline']));
            }
            if($server->getPlayerExact($this->requestor)->hasPermission('statspe.cmd.stats.advancedinfo')){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['ClientID'], $this->lang['Player']['StatClientID']));
                @$server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['UUID'], $this->lang['Player']['StatUUID']));
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['XBoxAuthenticated'], $this->lang['Player']['StatXBoxAuthenticated']));
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['LastIP'], $this->lang['Player']['StatLastIP']));
            }
            if($this->switch['FirstJoin']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['FirstJoin'], $this->lang['Player']['StatFirstJoin']));
            }
            if($this->switch['LastJoin']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['LastJoin'], $this->lang['Player']['StatLastJoin']));
            }
            if($this->switch['JoinCount']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['JoinCount'], $this->lang['Player']['StatJoinCount']));
            }
            if($this->switch['KillCount']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['KillCount'], $this->lang['Player']['StatKillCount']));
            }
            if($this->switch['DeathCount']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['DeathCount'], $this->lang['Player']['StatDeathCount']));
            }
            if($data['DeathCount'] > 0 && $this->switch['K/D']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_replace('{value}', $data['KillCount'] / $data['DeathCount'], $this->lang['Player']['StatK/D']));
            }
            if($this->switch['KickCount']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['KickCount'], $this->lang['Player']['StatKickCount']));
            }
            if($this->switch['OnlineTime']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['OnlineTime'], $this->lang['Player']['StatOnlineTime']));
            }
            if($this->switch['BlockBreakCount']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['BlocksBreaked'], $this->lang['Player']['StatBlockBreakCount']));
            }
            if($this->switch['BlockPlaceCount']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['BlocksPlaced'], $this->lang['Player']['StatBlockPlaceCount']));
            }
            if($this->switch['ChatCount']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['ChatMessages'], $this->lang['Player']['StatChatMessageCount']));
            }
            if($this->switch['FishCount']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['FishCount'], $this->lang['Player']['StatFishCount']));
            }
            if($this->switch['BedEnterCount']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['EnterBedCount'], $this->lang['Player']['StatBedEnterCount']));
            }
            if($this->switch['EatCount']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['EatCount'], $this->lang['Player']['StatEatCount']));
            }
            if($this->switch['CraftCount']){
                $server->getPlayerExact($this->requestor)->sendMessage(TF::AQUA.str_ireplace('{value}', $data['CraftCount'], $this->lang['Player']['StatCraftCount']));
            }
          }elseif(method_exists($this->requestor, 'getName')){
            $this->requestor->sendMessage(TF::GOLD.str_ireplace('{value}', $data['PlayerName'], $this->lang['Player']['StatsFor']));
            if($this->requestor->hasPermission('statspe.cmd.stats.advancedinfo')){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['ClientID'], $this->lang['Player']['StatClientID']));
                @$this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['UUID'], $this->lang['Player']['StatUUID']));
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['XBoxAuthenticated'], $this->lang['Player']['StatXBoxAuthenticated']));
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['LastIP'], $this->lang['Player']['StatLastIP']));
            }
            if($this->switch['FirstJoin']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['FirstJoin'], $this->lang['Player']['StatFirstJoin']));
            }
            if($this->switch['LastJoin']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['LastJoin'], $this->lang['Player']['StatLastJoin']));
            }
            if($this->switch['JoinCount']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['JoinCount'], $this->lang['Player']['StatJoinCount']));
            }
            if($this->switch['KillCount']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['KillCount'], $this->lang['Player']['StatKillCount']));
            }
            if($this->switch['DeathCount']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['DeathCount'], $this->lang['Player']['StatDeathCount']));
            }
            if($data['DeathCount'] > 0 && $this->switch['K/D']){
                $this->requestor->sendMessage(str_replace('{value}', $data['KillCount'] / $data['DeathCount'], $this->lang['Player']['K/D']));
            }
            if($this->switch['KickCount']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['KickCount'], $this->lang['Player']['StatKickCount']));
            }
            if($this->switch['OnlineTime']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['OnlineTime'], $this->lang['Player']['StatOnlineTime']));
            }
            if($this->switch['BlockBreakCount']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['BlocksBreaked'], $this->lang['Player']['StatBlocksBreakCount']));
            }
            if($this->switch['BlockPlaceCount']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['BlocksPlaced'], $this->lang['Player']['StatBlocksPlaceCount']));
            }
            if($this->switch['ChatCount']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['ChatMessages'], $this->lang['Player']['StatChatMessageCount']));
            }
            if($this->switch['FishCount']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['FishCount'], $this->lang['Player']['StatFishCount']));
            }
            if($this->switch['BedEnterCount']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['EnterBedCount'], $this->lang['Player']['StatBedEnterCount']));
            }
            if($this->switch['EatCount']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['EatCount'], $this->lang['Player']['StatEatCount']));
            }
            if($this->switch['CraftCount']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['CraftCount'], $this->lang['Player']['StatCraftCount']));
            }
            if($this->switch['DroppedItems']){
                $this->requestor->sendMessage(TF::AQUA.str_ireplace('{value}', $data['DroppedItems'], $this->lang['Player']['StatDroppedItems']));
            }
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
