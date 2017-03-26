<?php
namespace SalmonDE\StatsPE;

class Listener implements \pocketmine\event\Listener
{

    public function onJoin(\pocketmine\event\player\PlayerJoinEvent $event){
        $dataProvider = Base::getInstance()->getDataProvider();
        if(!is_array($data = $dataProvider->getAllData($event->getPlayer()->getName()))){
            foreach($dataProvider->getEntries() as $entry){ // Run through all entries and save the default values
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
                $dataProvider->saveData($event->getPlayer()->getName(), $entry, $value);
            }
        }else{
            if($dataProvider->entryExists('JoinCount')){ // Increase the join counter
                $dataProvider->saveData($event->getPlayer()->getName(), $dataProvider->getEntry('JoinCount'), ++$data['JoinCount']);
            }
        }
        if($dataProvider->entryExists('ClientID')){
            $dataProvider->saveData($event->getPlayer()->getName(), $dataProvider->getEntry('ClientID'), $event->getPlayer()->getClientId());
        }
        if($dataProvider->entryExists('LastIP')){
            $dataProvider->saveData($event->getPlayer()->getName(), $dataProvider->getEntry('LastIP'), $event->getPlayer()->getAddress());
        }
        if($dataProvider->entryExists('UUID')){
            $dataProvider->saveData($event->getPlayer()->getName(), $dataProvider->getEntry('UUID'), $event->getPlayer()->getUniqueId());
        }
        if($dataProvider->entryExists('XBoxAuthenticated')){
            $dataProvider->saveData($event->getPlayer()->getName(), $dataProvider->getEntry('XBoxAuthenticated'), false);
        }
        if($dataProvider->entryExists('RealName')){
            $dataProvider->saveData($event->getPlayer()->getName(), $dataProvider->getEntry('RealName'), $event->getPlayer()->getName());
        }
    }

    public function onQuit(\pocketmine\event\player\PlayerQuitEvent $event){
        $time = round(microtime(true) - ($event->getPlayer()->getLastPlayed() / 1000)); // Onlinetime in seconds 
        if(Base::getInstance()->getDataProvider()->entryExists('OnlineTime')){
            $dataProvider->saveData($event->getPlayer()->getName(), $dataProvider->getEntry('OnlineTime'), $time);
        }
    }
}
