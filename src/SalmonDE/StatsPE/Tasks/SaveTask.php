<?php
namespace SalmonDE\StatsPE\Tasks;

class SaveTask extends \pocketmine\scheduler\PluginTask
{

    public function __construct(\SalmonDE\StatsPE\Base $owner){
        parent::__construct($owner);
    }

    public function onRun($ct){
        $this->getOwner()->getDataProvider()->saveAll();
    }
}
