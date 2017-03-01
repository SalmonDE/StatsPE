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

    public function getData(string $player, Entry $entry){
        if($this->entryExists($entry->getName())){
            if(!$entry->shouldSave()){
                return;
            }
            $v = $this->dataConfig->getNested(strtolower($player).$entry->getName());
            if($entry->isValidType($v)){
                return $v;
            }
            Base::getInstance()->getLogger()->warning($msg = 'Unexpected datatype returned "'.gettype($v).'" for entry "'.$entry->getName().'" in "'.self::class.'" by "'.__FUNCTION__.'"!'); // ToDo: Move this in the events
            throw new \Exception($msg);
        }
    }

    public function getAllData(string $player = null){
        if($player !== null){
            return $this->dataConfig->get(strtolower($player));
        }
        return $this->dataConfig->getAll();
    }

    public function saveData(string $player, Entry $entry, $value){
        if($this->entryExists($entry->getName()) && $entry->isValidType($value) && $entry->shouldSave()){
            $this->dataConfig->setNested(strtolower($player).$entry->getName(), $value);
        }
    }

    public function addEntry(Entry $entry){
        if(!$this->entryExists($entry->getName()) && $entry->isValid()){
            $this->entries[$entry->getName()] = $entry;
            return true;
        }
        return false;
    }

    public function removeEntry(Entry $entry){
        if($this->entryExists($entry->getName())){
            unset($this->entries[$entry->getName()]);
        }
    }

    public function getEntries() : array{
        return $this->entries;
    }

    public function entryExists(string $entry) : bool{
        @return $this->entries[$entry] instanceof Entry;
    }

    public function saveAll(){
        $this->dataConfig->save();
    }
}
