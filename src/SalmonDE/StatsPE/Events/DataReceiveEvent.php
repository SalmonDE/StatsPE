<?php
namespace SalmonDE\StatsPE\Events;

use pocketmine\plugin\Plugin;
use SalmonDE\StatsPE\Entries\Entry;

class DataReceiveEvent extends DataEvent {

    public static $handlerList = null;

    public function __construct(Plugin $plugin, $data, string $player = null, Entry $entry = null){
        parent::__construct($plugin, $data, $player, $entry);
    }

}
