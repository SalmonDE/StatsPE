<?php
namespace SalmonDE\StatsPE;

class Listener implements \pocketmine\event\Listener
{

    public function onJoin(\pocketmine\event\player\PlayerJoinEvent $event){
        $dataProvider = Base::getInstance()->getDataProvider();
        if(!is_array($data = $dataProvider->getAllData($event->getPlayer()->getName()))){
            foreach($dataProvider->getEntries() as $entry){
                switch($entry->getName()){
                    case 'ClientID':
                        continue 2;

                    case 'LastIP':
                        continue 2;

                    case 'UUID':
                        continue 2;

                    case 'XBoxAuthenticated':
                        continue 2;

                    case 'RealName':
                        continue 2;

                    default:
                        $value = $entry->getDefault();
                }
                $dataProvider->saveData($event->getPlayer()->getName(), $entry, $value);
            }
        }else{
            if($dataProvider->entryExists('JoinCount')){
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
}
