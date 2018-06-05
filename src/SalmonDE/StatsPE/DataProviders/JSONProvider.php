<?php
namespace SalmonDE\StatsPE\DataProviders;

use pocketmine\Player;
use pocketmine\utils\Config;
use SalmonDE\StatsPE\Events\DataReceiveEvent;
use SalmonDE\StatsPE\Events\DataSaveEvent;
use SalmonDE\StatsPE\Entries\Entry;
use SalmonDE\StatsPE\StatsBase;

class JSONProvider extends DataProvider {

    private $owner;
    private $entries = [];
    private $dataConfig = null;

    public function __construct(StatsBase $owner, string $path){
        $this->owner = $owner;
        $this->initialize(['path' => $path]);
    }

    public function initialize(array $data){
        $this->dataConfig = new Config($data['path'], Config::JSON);
    }

    public function addPlayer(Player $player){
        foreach($this->entries as $entry){ // Run through all entries and save the default values
            $this->saveData($player->getName(), $entry, $entry->getDefaultValue());
        }
    }

    public function getData(string $player, Entry $entry){
        if($this->entryExists($entry->getName())){
            if(!$entry->shouldSave()){
                return;
            }
            $v = $this->dataConfig->getNested(strtolower($player).'.'.$entry->getName());

            $event = new DataReceiveEvent($this->owner, $v, $player, $entry);
            $this->owner->getServer()->getPluginManager()->callEvent($event);
            return $event->getData();
        }
    }

    public function getDataWhere(Entry $needleEntry, $needle, array $wantedEntries){
        if($this->entryExists($needleEntry->getName()) && $needleEntry->shouldSave()){
            if($wantedEntries === []){
                return [];
            }

            foreach($this->getAllData() as $player => $playerData){
                foreach($wantedEntries as $entry){
                    if(!$entry->shouldSave()){
                        $resultData[$player][$entry->getName()] = null;
                        continue;
                    }

                    $resultData[$player][$entry->getName()] = $playerData[$entry->getName()];
                }
            }
            return $resultData;
        }
    }

    public function getAllData(string $player = null){
        if($player !== null){

            $event = new DataReceiveEvent($this->owner, $this->dataConfig->get(strtolower($player), null));
            $this->owner->getServer()->getPluginManager()->callEvent($event);
            return $event->getData();
        }

        $event = new DataReceiveEvent($this->owner, $this->dataConfig->getAll());
        $this->owner->getServer()->getPluginManager()->callEvent($event);
        return $event->getData();
    }

    public function saveData(string $player, Entry $entry, $value){
        if($this->entryExists($entry->getName()) && $entry->shouldSave()){
            if($entry->isValidType($value)){

                $event = new DataSaveEvent($this->owner, $value, $player, $entry);
                $this->owner->getServer()->getPluginManager()->callEvent($event);
                if(!$event->isCancelled()){
                    $this->dataConfig->setNested(strtolower($player).'.'.$entry->getName(), $value);
                }
            }else{
                $this->owner->getLogger()->error($msg = 'Unexpected datatype "'.gettype($value).'" given for entry "'.$entry->getName().'" in "'.self::class.'" by "'.__FUNCTION__.'"!');
            }
        }
    }

    public function incrementValue(string $player, Entry $entry, int $int = 1){
        if($this->entryExists($entry->getName()) && $entry->shouldSave() && $entry->getExpectedType() === Entry::INT){
            $this->saveData($player, $entry, $this->getData($player, $entry) + $int);
        }
    }

    public function countDataRecords(): int{
        return count($this->getAllData());
    }

    public function saveAll(){
        $this->dataConfig->save();
    }

}
