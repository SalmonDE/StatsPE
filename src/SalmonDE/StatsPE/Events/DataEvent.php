<?php
namespace SalmonDE\StatsPE\Events;

use pocketmine\plugin\Plugin;
use SalmonDE\StatsPE\Entries\Entry;

class DataEvent extends StatsPE_Event
{
    /*
    SAVE for saving data
    RECEIVE for getting data
    */

    public static $handlerList = null;

    private $player;
    private $entry;
    private $data;

    public function __construct(Plugin $plugin, $data, string $player = null, Entry $entry = null){
        parent::__construct($plugin);
        $this->player = $player;
        $this->entry = $entry;
        $this->data = $data;
    }

    public function getPlayerName(): string{
        return $this->player;
    }

    public function getEntry(){
        return $this->entry;
    }

    public function getData(){
        return $this->data;
    }

    public function setData($data){
        if($this->entry === null || $this->entry->isValidType($data)){
            $this->data = $data;
        }
    }
}
