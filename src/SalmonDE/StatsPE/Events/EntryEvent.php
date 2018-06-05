<?php
namespace SalmonDE\StatsPE\Events;

use pocketmine\event\Cancellable;
use pocketmine\plugin\Plugin;
use SalmonDE\StatsPE\Entries\Entry;

class EntryEvent extends StatsPE_Event implements Cancellable {

    public static $handlerList = null;

    const ADD = 0;
    const REMOVE = 1;

    private $entry;
    private $eventType;

    public function __construct(Plugin $plugin, Entry $entry, int $eventType){
        parent::__construct($plugin);
        $this->entry = $entry;
        $this->eventType = $eventType;
    }

    public function getEntry(): Entry{
        return $this->entry;
    }

    public function getEventType(): int{
        return $this->eventType;
    }

}
