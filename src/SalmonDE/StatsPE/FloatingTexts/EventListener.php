<?php
namespace SalmonDE\StatsPE\FloatingTexts;

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use SalmonDE\StatsPE\FloatingTexts\FloatingTextManager;

class EventListener implements Listener {

    /**
     * @priority MONITOR
     */
    public function onJoin(PlayerJoinEvent $event){
        $floatingTexts = FloatingTextManager::getInstance()->getAllFloatingTexts();
        if(isset($floatingTexts[$event->getPlayer()->getLevel()->getFolderName()])){
            foreach($floatingTexts[$event->getPlayer()->getLevel()->getFolderName()] as $floatingText){
                $floatingText->sendTextToPlayer($event->getPlayer());
            }
        }
    }

    /**
     * @priority MONITOR
     */
    public function onEntityLevelChange(EntityLevelChangeEvent $event){
        if(!$event->isCancelled()){
            if($event->getEntity() instanceof Player){
                $floatingTexts = FloatingTextManager::getInstance()->getAllFloatingTexts();

                if(isset($floatingTexts[$event->getOrigin()->getFolderName()])){
                    foreach($floatingTexts[$event->getOrigin()->getFolderName()] as $floatingText){
                        $floatingText->removeTextForPlayer($event->getEntity());
                    }
                }

                if(isset($floatingTexts[$event->getTarget()->getFolderName()])){
                    foreach($floatingTexts[$event->getTarget()->getFolderName()] as $floatingText){
                        $floatingText->sendTextToPlayer($event->getEntity());
                    }
                }
            }
        }
    }

}
