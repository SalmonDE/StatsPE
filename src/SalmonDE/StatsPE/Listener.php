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
                $dataProvider->saveData($player->getName(), $entry->getName(), $value));
            }
        }else{
            if($dataProvider->entryExists('JoinCount')){
                $dataProvider->saveData($player->getName(), 'JoinCount', ++$data['JoinCount']);
            }
        }
        if($dataProvider->entryExists('ClientID')){
            $dataProvider->saveData($player->getName(), 'ClientID', $player->getClientId());
        }
        if($dataProvider->entryExists('LastIP')){
            $dataProvider->saveData($player->getName(), 'LastIP', $player->getAddress());
        }
        if($dataProvider->entryExists('UUID')){
            $dataProvider->saveData($player->getName(), 'UUID', $player->getUniqueId());
        }
        if($dataProvider->entryExists('XBoxAuthenticated')){
            $dataProvider->saveData($player->getName(), 'XBoxAuthenticated', false);
        }
        $dataProvider->saveData($player->getName(), 'RealName', $player->getName());
    }
}
