<?php
namespace StatsPE\Tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\TextFormat as TF;

class ShowStatsTask extends AsyncTask
{
  public function __construct($owner, $player){
      $this->player = strtolower($player);

  }
}
