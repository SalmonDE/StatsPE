<?php
namespace SalmonDE\StatsPE\Providers;

use pocketmine\utils\Config;
use SalmonDE\StatsPE\Base;

class JSONProvider implements DataProvider
{
    private $entries = [];
    private $dataConfig = null;

    public function __construct(string $path){
        $this->initialize(['path' => $path]);
    }

    public function initialize(array $data){
        $this->dataConfig = new Config($data['path'], Config::JSON);
    }

    public function addPlayer(\pocketmine\Player $player){
        if(!is_array($this->dataConfig->get($player->getName()))){
            foreach($this->entries as $entry){
                $this->saveData($player->getName(), $entry->getName(), $entry->getDefault());
            }
            $this->saveAll(true);
        }
    }

    public function getData(string $player, Entry $entry){
        if($this->entryExists($entry)){
            $v = $this->dataConfig->getNested(strtolower($player).$entry->getName());
            if($entry->isValidType($v)){
                return $v;
            }
            Base::getInstance()->getLogger()->warning($msg = 'Unexpected datatype returned "'.gettype($v).'" for entry "'.$entry.'" in "'.self::class.'" by "'.__FUNCTION__.'"!'); // ToDo: Move this in the events
            throw new \Exception($msg);
        }
    }

    public function getAllData() : array{
        return $this->dataConfig->getAll();
    }

    public function saveData(string $player, Entry $entry, $value){
        if($this->entryExists($entry) && $entry->isValidType($value)){
            $this->dataConfig->setNested(strtolower($player).$entry, $value);
        }
    }

    public function addEntry(Entry $entry){
        if(!$this->entryExists($entry) && $entry->isValid()){
            $this->entries[$entry->getName()] = $entry;
        }
    }

    public function removeEntry(Entry $entry){
        if($this->entryExists($entry)){
            unset($this->entries[$entry->getName()]);
        }
    }

    public function getEntries() : array{
        return $this->entries;
    }

    public function entryExists(Entry $entry) : bool{
        if(isset($this->entries[$entry->getName()])){
            return true;
        }
        return false;
    }

    public function saveAll(){
        $this->dataConfig->save();
    }
}
