<?php
namespace SalmonDE\StatsPE\Events;

use pocketmine\event\Cancellable;
use pocketmine\plugin\Plugin;
use SalmonDE\StatsPE\Events\DataEvent;
use SalmonDE\StatsPE\Providers\Entry;

class DataSaveEvent extends DataEvent implements Cancellable {

    public static $handlerList = null;

    public function __construct(Plugin $plugin, $data, string $player, Entry $entry){
        parent::__construct($plugin, $data, $player, $entry);
    }

}
