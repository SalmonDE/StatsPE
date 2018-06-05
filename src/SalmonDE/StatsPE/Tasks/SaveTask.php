<?php
namespace SalmonDE\StatsPE\Tasks;

use pocketmine\scheduler\PluginTask;
use SalmonDE\StatsPE\StatsBase;

class SaveTask extends PluginTask
{

    public function __construct(StatsBase $owner){
        parent::__construct($owner);
    }

    public function onRun(int $ct){
        $this->getOwner()->getDataProvider()->saveAll();
    }
}
