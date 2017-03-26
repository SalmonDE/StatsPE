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
                switch($entry->getName()){
                    case 'ClientID':
                    case 'LastIP':
                    case 'UUID':
                    case 'XBoxAuthenticated':
                    case 'RealName':
                        continue 2; //skip some entries which will be set later on

                    default:
                        $value = $entry->getDefault();
                }
                $this->dataProvider->saveData($event->getPlayer()->getName(), $entry, $value);
            }
        }else{
            if($this->dataProvider->entryExists('JoinCount')){ // Increase the join counter
                $this->dataProvider->saveData($event->getPlayer()->getName(), $this->dataProvider->getEntry('JoinCount'), ++$data['JoinCount']);
            }
        }
        if($this->dataProvider->entryExists('ClientID')){
            $this->dataProvider->saveData($event->getPlayer()->getName(), $this->dataProvider->getEntry('ClientID'), $event->getPlayer()->getClientId());
        }
        if($this->dataProvider->entryExists('LastIP')){
            $this->dataProvider->saveData($event->getPlayer()->getName(), $this->dataProvider->getEntry('LastIP'), $event->getPlayer()->getAddress());
        }
        if($this->dataProvider->entryExists('UUID')){
            $this->dataProvider->saveData($event->getPlayer()->getName(), $this->dataProvider->getEntry('UUID'), $event->getPlayer()->getUniqueId());
        }
        if($this->dataProvider->entryExists('XBoxAuthenticated')){
            $this->dataProvider->saveData($event->getPlayer()->getName(), $this->dataProvider->getEntry('XBoxAuthenticated'), false);
        }
        if($this->dataProvider->entryExists('RealName')){
            $this->dataProvider->saveData($event->getPlayer()->getName(), $this->dataProvider->getEntry('RealName'), $event->getPlayer()->getName());
        }
    }

    public function onQuit(\pocketmine\event\player\PlayerQuitEvent $event){
        $time = round(microtime(true) - ($event->getPlayer()->getLastPlayed() / 1000)); // Onlinetime in seconds
        if($this->dataProvider->entryExists('OnlineTime')){
            $this->dataProvider->saveData($event->getPlayer()->getName(), $this->dataProvider->getEntry('OnlineTime'), $time);
        }
    }

    public function onPlayerDeath(\pocketmine\event\player\PlayerDeathEvent $event){
        if($this->dataProvider->entryExists('DeathCount')){
            $this->dataProvider->saveData($name = $event->getPlayer()->getName(), $ent = $this->dataProvider->getEntry('DeathCount'), $this->dataProvider->getData($name, $ent) + 1);
        }

        if($this->dataProvider->entryExists('KillCount')){
            if($cause = $event->getPlayer()->getLastDamageCause() instanceof \pocketmine\event\entity\EntityDamageByEntityEvent){
                if($dmgr = $cause->getDamager() instanceof Player){
                    $this->dataProvider->saveData($dmgr->getName(), $ent = $this->dataProvider->getEntry('KillCount'), $this->dataProvider->getData($dmgr->getName(), $ent) + 1);
                }
            }
        }
    }
}
