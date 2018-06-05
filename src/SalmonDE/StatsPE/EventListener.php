<?php
namespace SalmonDE\StatsPE;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use SalmonDE\StatsPE\Events\EntryEvent;

class EventListener implements Listener
{
    private $owner;

    public function __construct(StatsBase $owner){
        $this->owner = $owner;
    }

    /**
    * @priority HIGHEST
    */
    public function onEntryEvent(EntryEvent $event): void{
        if($event->getEventType() === EntryEvent::REMOVE && $event->getEntry()->getName() === 'Username'){
            $event->setCancelled();
        }
    }

    public function onJoin(PlayerJoinEvent $event){
        if(!is_array($data = $this->owner->getDataProvider()->getAllData($event->getPlayer()->getName()))){
            $this->owner->getDataProvider()->addPlayer($event->getPlayer());

        }else{ // Prevent setting the join counter to 2 on first join
            if(StatsBase::getEntryManager()->entryExists('JoinCount')){
                $this->owner->getDataProvider()->saveData($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('JoinCount'), ++$data['JoinCount']);
            }
        }

        if(StatsBase::getEntryManager()->entryExists('Online')){
            $this->owner->getDataProvider()->saveData($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('Online'), true);
        }

        if(StatsBase::getEntryManager()->entryExists('FirstJoin')){
            $this->owner->getDataProvider()->saveData($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('FirstJoin'), $event->getPlayer()->getFirstPlayed() / 1000);
        }

        if(StatsBase::getEntryManager()->entryExists('LastJoin')){
            $this->owner->getDataProvider()->saveData($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('LastJoin'), $event->getPlayer()->getLastPlayed() / 1000);
        }

        if(StatsBase::getEntryManager()->entryExists('ClientID')){
            $this->owner->getDataProvider()->saveData($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('ClientID'), (string) $event->getPlayer()->getClientId());
        }

        if(StatsBase::getEntryManager()->entryExists('LastIP')){
            $this->owner->getDataProvider()->saveData($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('LastIP'), (string) $event->getPlayer()->getAddress());
        }

        if(StatsBase::getEntryManager()->entryExists('UUID')){
            $this->owner->getDataProvider()->saveData($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('UUID'), $event->getPlayer()->getUniqueId()->toString());
        }

        if(StatsBase::getEntryManager()->entryExists('XBoxAuthenticated')){
            $this->owner->getDataProvider()->saveData($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('XBoxAuthenticated'), false);
        }

        if(StatsBase::getEntryManager()->entryExists('Username')){
            $this->owner->getDataProvider()->saveData($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('Username'), $event->getPlayer()->getName());
        }
    }

    public function onQuit(PlayerQuitEvent $event){
        if(StatsBase::getEntryManager()->entryExists('OnlineTime')){
            if(\pocketmine\START_TIME < ($event->getPlayer()->getLastPlayed() / 1000)){
                $time = ceil(microtime(true) - ($event->getPlayer()->getLastPlayed() / 1000)); // Onlinetime in seconds
                $this->owner->getDataProvider()->incrementValue($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('OnlineTime'), $time);
            }else{
                $this->owner->getLogger()->warning('Couldn\'t save online time for player "'.$event->getPlayer()->getName().'" because it exceeds the server running time!');
            }
        }

        if(StatsBase::getEntryManager()->entryExists('Online')){
            $this->owner->getDataProvider()->saveData($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('Online'), false);
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $event){
        if(StatsBase::getEntryManager()->entryExists('DeathCount')){
            $this->owner->getDataProvider()->incrementValue($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('DeathCount'));
        }

        if(StatsBase::getEntryManager()->entryExists('KillCount')){
            if(($cause = $event->getPlayer()->getLastDamageCause()) instanceof EntityDamageByEntityEvent){
                if(($dmgr = $cause->getDamager()) instanceof Player){
                    $this->owner->getDataProvider()->incrementValue($dmgr->getName(), StatsBase::getEntryManager()->getEntry('KillCount'));
                }
            }
        }
    }


    /**
    * @priority MONITOR
    */
    public function onBlockBreak(BlockBreakEvent $event){
        if(!$event->isCancelled()){
            if(StatsBase::getEntryManager()->entryExists('BlockBreakCount')){
                $this->owner->getDataProvider()->incrementValue($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('BlockBreakCount'));
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onBlockPlace(BlockPlaceEvent $event){
        if(!$event->isCancelled()){
            if(StatsBase::getEntryManager()->entryExists('BlockPlaceCount')){
                $this->owner->getDataProvider()->incrementValue($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('BlockPlaceCount'));
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onChat(PlayerChatEvent $event){
        if(!$event->isCancelled()){
            if(StatsBase::getEntryManager()->entryExists('ChatCount')){
                $this->owner->getDataProvider()->incrementValue($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('ChatCount'));
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onItemConsume(PlayerItemConsumeEvent $event){
        if(!$event->isCancelled()){
            if(StatsBase::getEntryManager()->entryExists('ItemConsumeCount')){
                $this->owner->getDataProvider()->incrementValue($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('ItemConsumeCount'));
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onCraftItem(CraftItemEvent $event){
        if(!$event->isCancelled()){
            if(StatsBase::getEntryManager()->entryExists('ItemCraftCount')){
                $this->owner->getDataProvider()->incrementValue($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('ItemCraftCount'));
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onDropItem(PlayerDropItemEvent $event){
        if(!$event->isCancelled()){
            if(StatsBase::getEntryManager()->entryExists('ItemDropCount')){
                $this->owner->getDataProvider()->incrementValue($event->getPlayer()->getName(), StatsBase::getEntryManager()->getEntry('ItemDropCount'));
            }
        }
    }
}
