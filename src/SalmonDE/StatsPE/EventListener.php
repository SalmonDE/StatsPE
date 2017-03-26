<?php
namespace SalmonDE\StatsPE;

use pocketmine\Player;

class EventListener implements \pocketmine\event\Listener
{

    private $dataProvider = null;

    public function __construct(){
        $this->dataProvider = Base::getInstance()->getDataProvider();
    }

    public function onJoin(\pocketmine\event\player\PlayerJoinEvent $event){
        if(!is_array($data = $this->dataProvider->getAllData($event->getPlayer()->getName()))){
            foreach($this->dataProvider->getEntries() as $entry){ // Run through all entries and save the default values
                $this->dataProvider->saveData($event->getPlayer()->getName(), $entry, $entry->getDefault());
            }
        }else{
            if($this->dataProvider->entryExists('JoinCount')){ // Increase the join counter
                $this->dataProvider->saveData($event->getPlayer()->getName(), $this->dataProvider->getEntry('JoinCount'), ++$data['JoinCount']);
            }
        }
        if($this->dataProvider->entryExists('ClientID')){
            $this->dataProvider->saveData($event->getPlayer()->getName(), $this->dataProvider->getEntry('ClientID'), (string) $event->getPlayer()->getClientId());
        }
        if($this->dataProvider->entryExists('LastIP')){
            $this->dataProvider->saveData($event->getPlayer()->getName(), $this->dataProvider->getEntry('LastIP'), (string) $event->getPlayer()->getAddress());
        }
        if($this->dataProvider->entryExists('UUID')){
            $this->dataProvider->saveData($event->getPlayer()->getName(), $this->dataProvider->getEntry('UUID'), $event->getPlayer()->getUniqueId()->toString());
        }
        if($this->dataProvider->entryExists('XBoxAuthenticated')){
            $this->dataProvider->saveData($event->getPlayer()->getName(), $this->dataProvider->getEntry('XBoxAuthenticated'), false);
        }
        if($this->dataProvider->entryExists('Username')){
            $this->dataProvider->saveData($event->getPlayer()->getName(), $this->dataProvider->getEntry('Username'), $event->getPlayer()->getName());
        }
    }

    public function onQuit(\pocketmine\event\player\PlayerQuitEvent $event){ // This seems to fail when the server stops with /stop
        $time = round(microtime(true) - ($event->getPlayer()->getLastPlayed() / 1000)); // Onlinetime in seconds
        if($this->dataProvider->entryExists('OnlineTime')){
            $this->dataProvider->saveData($name = $event->getPlayer()->getName(), $ent = $this->dataProvider->getEntry('OnlineTime'), intval($this->dataProvider->getData($name, $ent) + $time));
        }
    }

    public function onPlayerDeath(\pocketmine\event\player\PlayerDeathEvent $event){
        if($this->dataProvider->entryExists('DeathCount')){
            $this->dataProvider->saveData($name = $event->getPlayer()->getName(), $ent = $this->dataProvider->getEntry('DeathCount'), $this->dataProvider->getData($name, $ent) + 1);
        }

        if($this->dataProvider->entryExists('KillCount')){
            if(($cause = $event->getPlayer()->getLastDamageCause()) instanceof \pocketmine\event\entity\EntityDamageByEntityEvent){
                if($dmgr = $cause->getDamager() instanceof Player){
                    $this->dataProvider->saveData($dmgr->getName(), $ent = $this->dataProvider->getEntry('KillCount'), $this->dataProvider->getData($dmgr->getName(), $ent) + 1);
                }
            }
        }
    }


    /**
    * @priority MONITOR
    */
    public function onBlockBreak(\pocketmine\event\block\BlockBreakEvent $event){
        if(!$event->isCancelled()){
            if($this->dataProvider->entryExists('BlockBreakCount')){
                $this->dataProvider->saveData($name = $event->getPlayer()->getName(), $ent = $this->dataProvider->getEntry('BlockBreakCount'), $this->dataProvider->getData($name, $ent) + 1);
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onBlockPlace(\pocketmine\event\block\BlockPlaceEvent $event){
        if(!$event->isCancelled()){
            if($this->dataProvider->entryExists('BlockPlaceCount')){
                $this->dataProvider->saveData($name = $event->getPlayer()->getName(), $ent = $this->dataProvider->getEntry('BlockPlaceCount'), $this->dataProvider->getData($name, $ent) + 1);
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onChat(\pocketmine\event\player\PlayerChatEvent $event){
        if(!$event->isCancelled()){
            if($this->dataProvider->entryExists('ChatCount')){
                $this->dataProvider->saveData($name = $event->getPlayer()->getName(), $ent = $this->dataProvider->getEntry('ChatCount'), $this->dataProvider->getData($name, $ent) + 1);
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onItemConsume(\pocketmine\event\player\PlayerItemConsumeEvent $event){
        if(!$event->isCancelled()){
            if($this->dataProvider->entryExists('ItemConsumeCount')){
                $this->dataProvider->saveData($name = $event->getPlayer()->getName(), $ent = $this->dataProvider->getEntry('ItemConsumeCount'), $this->dataProvider->getData($name, $ent) + 1);
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onCraftItem(\pocketmine\event\inventory\CraftItemEvent $event){
        if(!$event->isCancelled()){
            if($this->dataProvider->entryExists('ItemCraftCount')){
                $this->dataProvider->saveData($name = $event->getPlayer()->getName(), $ent = $this->dataProvider->getEntry('ItemCraftCount'), $this->dataProvider->getData($name, $ent) + 1);
            }
        }
    }

    /**
    * @priority MONITOR
    */
    public function onDropItem(\pocketmine\event\player\PlayerDropItemEvent $event){
        if(!$event->isCancelled()){
            if($this->dataProvider->entryExists('ItemDropCount')){
                $this->dataProvider->saveData($name = $event->getPlayer()->getName(), $ent = $this->dataProvider->getEntry('ItemDropCount'), $this->dataProvider->getData($name, $ent) + 1);
            }
        }
    }
}
