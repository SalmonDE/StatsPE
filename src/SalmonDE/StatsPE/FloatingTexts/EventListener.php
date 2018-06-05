<?php
namespace SalmonDE\StatsPE\FloatingTexts;

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use SalmonDE\StatsPE\StatsBase;

class EventListener implements Listener {

    private $owner;

    public function __construct(StatsBase $owner){
        $this->owner = $owner;
    }

    /**
     * @priority MONITOR
     */
    public function onJoin(PlayerJoinEvent $event){
        $floatingTexts = $this->owner->getFloatingTextManager()->getAllFloatingTexts();
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
                $floatingTexts = $this->owner->getFloatingTextManager()->getAllFloatingTexts();

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
