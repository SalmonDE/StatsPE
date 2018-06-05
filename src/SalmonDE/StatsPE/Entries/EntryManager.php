<?php
declare(strict_types = 1);

namespace SalmonDE\StatsPE\Entries;

use pocketmine\event\Listener;
use SalmonDE\StatsPE\StatsBase;
use SalmonDE\StatsPE\Events\EntryEvent;

class EntryManager {

    static private $entries = [];
    private $owner;

    public function __construct(StatsBase $owner){
        $this->owner = $owner;
    }

    public function addEntry(Entry $entry): bool{
        if(!$this->entryExists($entry->getName())){

            $event = new EntryEvent($this->owner, $entry, EntryEvent::ADD);
            $this->owner->getServer()->getPluginManager()->callEvent($event);

            if(!$event->isCancelled()){
                self::$entries[strtolower($entry->getName())] = $entry;

                if($entry instanceof Listener){
                    $this->owner->getServer()->getPluginManager()->registerEvents($entry, $this->owner);
                }

                return true;
            }
        }

        return false;
    }

    public function removeEntry(Entry $entry): bool{
        if($this->entryExists($entry->getName())){

            $event = new EntryEvent($this->owner, $entry, EntryEvent::REMOVE);
            $this->owner->getServer()->getPluginManager()->callEvent($event);

            if(!$event->isCancelled()){
                unset(self::$entries[strtolower($entry->getName())]);
            }
        }
    }

    public function getAllEntries(): array{
        return self::$entries;
    }

    public function getEntry(string $entryName): ?Entry{
        return self::$entries[strtolower($entryName)] ?? null;
    }

    public function entryExists(string $entryName): bool{
        return isset(self::$entries[strtolower($entryName)]);
    }
}
