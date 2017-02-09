<?php
namespace SalmonDE\StatsPE\Tasks;

use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
use pocketmine\math\Vector3;

class SpawnFloatStatTask extends AsyncTask
{

  public function __construct($owner, $fstat, $player, $target = null){
      $this->lang = $owner->getMessages('Player');
      $this->mysql = $owner->getConfig()->get('MySQL');
      $this->fstat = $fstat;
      if(is_object($player)){
          $this->player = $player->getName();
      }else{
          $this->player = $player;
      }
      if(is_object($target)){
          $this->target = $target->getName();
      }else{
          $this->target = $target;
      }
  }

  public function onRun(){
      $connection = @mysqli_connect($this->mysql['host'], $this->mysql['user'], $this->mysql['password'], $this->mysql['database']);
      $this->setResult(mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM Stats WHERE PlayerName = '$this->player'")));
  }

  public function onCompletion(Server $server){
    $info = $this->getResult();
    $timediff = date_diff(new \DateTime($info['LastJoin']), new \DateTime(date('Y-m-d H:i:s')));
    $text['PlayerName'] = TF::GOLD.str_ireplace('{value}', $info['PlayerName'], $this->lang['StatsFor']);
    foreach($this->fstat['Stats'] as $stat){
        if($stat['Enabled']){
            if($stat['Name'] == 'K/D'){
                if($info['DeathCount'] > 0){
                    $text['K/D'] = TF::AQUA.str_ireplace('{value}', round($info['KillCount'] / $info['DeathCount'], 2), $this->lang['StatK/D']);
                }
            }elseif($stat['Name'] == 'OnlineTime'){
                if($info['Online'] == $this->lang['StatYes']){
                    $text['OnlineTime'] = TF::AQUA.str_ireplace(['{hours}', '{minutes}', '{seconds}'], [$timediff->h, $timediff->i, $timediff->s], $this->lang['StatOnlineTime']);
                }
            }else{
                $text[$stat['Name']] = TF::AQUA.str_ireplace('{value}', $info[$stat['Name']], $this->lang[$stat['Lang']]);
            }
        }
    }
    $text = implode("\n", $text);
    if($server->isLevelLoaded($this->fstat['Position']['Level'])){
        $server->getLevelByName($this->fstat['Position']['Level'])->addparticle(new FloatingTextParticle(new Vector3($this->fstat['Position']['X'], $this->fstat['Position']['Y'], $this->fstat['Position']['Z']), '', $text), [$server->getPlayerExact($this->target)]);
    }
  }
}
