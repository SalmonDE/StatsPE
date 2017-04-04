<?php
namespace SalmonDE\StatsPE;

use pocketmine\Player;

class EventListener implements \pocketmine\event\Listener
{

    public function onJoin(\pocketmine\event\player\PlayerJoinEvent $event){
        if(!is_array($data = Base::getInstance()->getDataProvider()->getAllData($event->getPlayer()->getName()))){
            Base::getInstance()->getDataProvider()->addPlayer($event->getPlayer());
        }else{
            if(Base::getInstance()->getDataProvider()->entryExists('JoinCount')){ // Increase the join counter
                Base::getInstance()->getDataProvider()->saveData($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('JoinCount'), ++$data['JoinCount']);
            }
        }

        if(Base::getInstance()->getDataProvider()->entryExists('Online')){
            Base::getInstance()->getDataProvider()->saveData($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('Online'), true);
        }

        if(Base::getInstance()->getDataProvider()->entryExists('FirstJoin')){ // I'd like to do this only once in $dataProvider()->addPlayer();
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

    public function onQuit(\pocketmine\event\player\PlayerQuitEvent $event){
        if(Base::getInstance()->getDataProvider()->entryExists('OnlineTime')){
            $time = round(microtime(true) - ($event->getPlayer()->getLastPlayed() / 1000)); // Onlinetime in seconds
            Base::getInstance()->getDataProvider()->saveData($name = $event->getPlayer()->getName(), $ent = Base::getInstance()->getDataProvider()->getEntry('OnlineTime'), intval(Base::getInstance()->getDataProvider()->getData($name, $ent) + $time));
        }

        if(Base::getInstance()->getDataProvider()->entryExists('Online')){
            Base::getInstance()->getDataProvider()->saveData($event->getPlayer()->getName(), Base::getInstance()->getDataProvider()->getEntry('Online'), false);
        }
    }

    public function onPlayerDeath(\pocketmine\event\player\PlayerDeathEvent $event){
        if(Base::getInstance()->getDataProvider()->entryExists('DeathCount')){
            Base::getInstance()->getDataProvider()->saveData($name = $event->getPlayer()->getName(), $ent = Base::getInstance()->getDataProvider()->getEntry('DeathCount'), Base::getInstance()->getDataProvider()->getData($name, $ent) + 1);
        }

        if(Base::getInstance()->getDataProvider()->entryExists('KillCount')){
            if(($cause = $event->getPlayer()->getLastDamageCause()) instanceof \pocketmine\event\entity\EntityDamageByEntityEvent){
                if(($dmgr = $cause->getDamager()) instanceof Player){
                    Base::getInstance()->getDataProvider()->saveData($dmgr->getName(), $ent = Base::getInstance()->getDataProvider()->getEntry('KillCount'), Base::getInstance()->getDataProvider()->getData($dmgr->getName(), $ent) + 1);
                }
            }
        }
    }


    /**
    * @priority MONITOR
    */
    public function onBlockBreak(\pocketmine\event\block\BlockBreakEvent $event){
        if(!$event->isCancelled()){
            if(Base::getInstance()->getDataProvider()->entryExists('BlockBreakCount')){
                Base::getInstance()->getDataProvider()->saveData($name = $event->getPlayer()->getName(), $ent = Base::getInstance()->getDataProvider()->getEntry('BlockBreakCount'), Base::getInstance()->getDataProvider()->getData($name, $ent) + 1);
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onBlockPlace(\pocketmine\event\block\BlockPlaceEvent $event){
        if(!$event->isCancelled()){
            if(Base::getInstance()->getDataProvider()->entryExists('BlockPlaceCount')){
                Base::getInstance()->getDataProvider()->saveData($name = $event->getPlayer()->getName(), $ent = Base::getInstance()->getDataProvider()->getEntry('BlockPlaceCount'), Base::getInstance()->getDataProvider()->getData($name, $ent) + 1);
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onChat(\pocketmine\event\player\PlayerChatEvent $event){
        if(!$event->isCancelled()){
            if(Base::getInstance()->getDataProvider()->entryExists('ChatCount')){
                Base::getInstance()->getDataProvider()->saveData($name = $event->getPlayer()->getName(), $ent = Base::getInstance()->getDataProvider()->getEntry('ChatCount'), Base::getInstance()->getDataProvider()->getData($name, $ent) + 1);
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onItemConsume(\pocketmine\event\player\PlayerItemConsumeEvent $event){
        if(!$event->isCancelled()){
            if(Base::getInstance()->getDataProvider()->entryExists('ItemConsumeCount')){
                Base::getInstance()->getDataProvider()->saveData($name = $event->getPlayer()->getName(), $ent = Base::getInstance()->getDataProvider()->getEntry('ItemConsumeCount'), Base::getInstance()->getDataProvider()->getData($name, $ent) + 1);
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onCraftItem(\pocketmine\event\inventory\CraftItemEvent $event){
        if(!$event->isCancelled()){
            if(Base::getInstance()->getDataProvider()->entryExists('ItemCraftCount')){
                Base::getInstance()->getDataProvider()->saveData($name = $event->getPlayer()->getName(), $ent = Base::getInstance()->getDataProvider()->getEntry('ItemCraftCount'), Base::getInstance()->getDataProvider()->getData($name, $ent) + 1);
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onDropItem(\pocketmine\event\player\PlayerDropItemEvent $event){
        if(!$event->isCancelled()){
            if(Base::getInstance()->getDataProvider()->entryExists('ItemDropCount')){
                Base::getInstance()->getDataProvider()->saveData($name = $event->getPlayer()->getName(), $ent = Base::getInstance()->getDataProvider()->getEntry('ItemDropCount'), Base::getInstance()->getDataProvider()->getData($name, $ent) + 1);
            }
        }
    }
}
