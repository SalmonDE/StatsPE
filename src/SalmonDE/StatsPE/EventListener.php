<?php
namespace SalmonDE\StatsPE;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
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
    /**
    * @priority HIGHEST
    */
    public function onEntryEvent(EntryEvent $event): void{
        if($event->getType() === EntryEvent::REMOVE && $event->getEntry()->getName() === 'Username'){
            $event->setCancelled();
        }
    }

    public function onJoin(PlayerJoinEvent $event){
        if(!is_array($data = Base::getInstance()->getDataProvider()->getAllData($event->getPlayer()->getName()))){
            Base::getInstance()->getDataProvider()->addPlayer($event->getPlayer());

        }else{ // Prevent setting the join counter to 2 on first join
            if(Base::getInstance()->getDataProvider()->entryExists('JoinCount')){
                Base::getInstance()->getDataProvider()->saveData($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('JoinCount'), ++$data['JoinCount']);
            }
        }

        if(Base::getInstance()->getDataProvider()->entryExists('Online')){
            Base::getInstance()->getDataProvider()->saveData($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('Online'), true);
        }

        if(Base::getInstance()->getDataProvider()->entryExists('FirstJoin')){
            Base::getInstance()->getDataProvider()->saveData($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('FirstJoin'), $event->getPlayer()->getFirstPlayed() / 1000);
        }

        if(Base::getInstance()->getDataProvider()->entryExists('LastJoin')){
            Base::getInstance()->getDataProvider()->saveData($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('LastJoin'), $event->getPlayer()->getLastPlayed() / 1000);
        }

        if(Base::getInstance()->getDataProvider()->entryExists('ClientID')){
            Base::getInstance()->getDataProvider()->saveData($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('ClientID'), (string) $event->getPlayer()->getClientId());
        }

        if(Base::getInstance()->getDataProvider()->entryExists('LastIP')){
            Base::getInstance()->getDataProvider()->saveData($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('LastIP'), (string) $event->getPlayer()->getAddress());
        }

        if(Base::getInstance()->getDataProvider()->entryExists('UUID')){
            Base::getInstance()->getDataProvider()->saveData($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('UUID'), $event->getPlayer()->getUniqueId()->toString());
        }

        if(Base::getInstance()->getDataProvider()->entryExists('XBoxAuthenticated')){
            Base::getInstance()->getDataProvider()->saveData($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('XBoxAuthenticated'), false);
        }

        if(Base::getInstance()->getDataProvider()->entryExists('Username')){
            Base::getInstance()->getDataProvider()->saveData($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('Username'), $event->getPlayer()->getName());
        }
    }

    public function onQuit(PlayerQuitEvent $event){
        if(Base::getInstance()->getDataProvider()->entryExists('OnlineTime')){
            if(\pocketmine\START_TIME < ($event->getPlayer()->getLastPlayed() / 1000)){
                $time = ceil(microtime(true) - ($event->getPlayer()->getLastPlayed() / 1000)); // Onlinetime in seconds
                Base::getInstance()->getDataProvider()->incrementValue($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('OnlineTime'), $time);
            }else{
                Base::getInstance()->getLogger()->warning('Couldn\'t save online time for player "'.$event->getPlayer()->getName().'" because it exceeds the server running time!');
            }
        }

        if(Base::getInstance()->getDataProvider()->entryExists('Online')){
            Base::getInstance()->getDataProvider()->saveData($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('Online'), false);
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $event){
        if(Base::getInstance()->getDataProvider()->entryExists('DeathCount')){
            Base::getInstance()->getDataProvider()->incrementValue($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('DeathCount'));
        }

        if(Base::getInstance()->getDataProvider()->entryExists('KillCount')){
            if(($cause = $event->getPlayer()->getLastDamageCause()) instanceof \pocketmine\event\entity\EntityDamageByEntityEvent){
                if(($dmgr = $cause->getDamager()) instanceof Player){
                    Base::getInstance()->getDataProvider()->incrementValue($dmgr->getName(), Base::getInstance()->getDataProvider()->getEntry('KillCount'));
                }
            }
        }
    }


    /**
    * @priority MONITOR
    */
    public function onBlockBreak(BlockBreakEvent $event){
        if(!$event->isCancelled()){
            if(Base::getInstance()->getDataProvider()->entryExists('BlockBreakCount')){
                Base::getInstance()->getDataProvider()->incrementValue($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('BlockBreakCount'));
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onBlockPlace(BlockPlaceEvent $event){
        if(!$event->isCancelled()){
            if(Base::getInstance()->getDataProvider()->entryExists('BlockPlaceCount')){
                Base::getInstance()->getDataProvider()->incrementValue($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('BlockPlaceCount'));
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onChat(PlayerChatEvent $event){
        if(!$event->isCancelled()){
            if(Base::getInstance()->getDataProvider()->entryExists('ChatCount')){
                Base::getInstance()->getDataProvider()->incrementValue($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('ChatCount'));
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onItemConsume(PlayerItemConsumeEvent $event){
        if(!$event->isCancelled()){
            if(Base::getInstance()->getDataProvider()->entryExists('ItemConsumeCount')){
                Base::getInstance()->getDataProvider()->incrementValue($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('ItemConsumeCount'));
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onCraftItem(CraftItemEvent $event){
        if(!$event->isCancelled()){
            if(Base::getInstance()->getDataProvider()->entryExists('ItemCraftCount')){
                Base::getInstance()->getDataProvider()->incrementValue($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('ItemCraftCount'));
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onDropItem(PlayerDropItemEvent $event){
        if(!$event->isCancelled()){
            if(Base::getInstance()->getDataProvider()->entryExists('ItemDropCount')){
                Base::getInstance()->getDataProvider()->incrementValue($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('ItemDropCount'));
            }
        }
    }
}
