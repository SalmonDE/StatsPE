<?php
namespace SalmonDE\StatsPE\Providers;

use pocketmine\utils\Config;
use SalmonDE\StatsPE\Base;

class JSONProvider implements DataProvider //The whole entry thing must be rewritten
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
        if(!is_array($this->dataConfig->get($n = strtolower($player->getName())))){
            foreach($this->entries as $entry => $data){
                $this->saveData($n, $entry, $data['default']);
            }
            $this->saveAll();
        }
    }

    public function getData(string $player, string $entry){
        if($this->validEntry($entry)){
            $v = $this->dataConfig->getNested($player.$entry);
            switch($this->getEntryType($entry)){
                case 0:
                    if(is_int($v)){
                        break;
                    }
                case 1:
                    if(is_float($v)){
                        break;
                    }
                case 2:
                    if(is_string($v)){
                        break;
                    }
                default:
                    Base::getInstance()->getLogger()->warning($msg = 'Unexpected datatype returned "'.gettype($v).'" for entry "'.$entry.'" in "'.self::class.'" by "'.__FUNCTION__.'"!'); // ToDo: Move this in the events
                    throw new \Exception($msg);
            }
            return $v;
        }
    }

    public function getAllData() : array{
        return $this->dataConfig->getAll();
    }

    public function saveData(string $player, Entry $entry, $value){
        if($this->validEntry($entry)){
            $this->dataConfig->setNested($player.$entry, $value);
        }
    }

    public function addEntry(Entry $entry){
        if(!$this->validEntry($entry, true)){
            $this->entries[$entry] = ['type' => $expectedType, 'default' => $default];
        }
    }

    public function removeEntry(Entry $entry){
        if($this->validEntry($entry)){
            unset($this->entries[$entry]);
        }
    }

    public function getEntries() : array{
        return $this->entries;
    }

    public function getEntryType(Entry $entry) : int{
        return $this->entries[$entry]['type'];
    }

    public function validEntry(Entry $entry, $silence = false) : bool{
        if(isset($this->entries[$entry])){
            return true;
        }
        if(!$silence){
            Base::getInstance()->getLogger()->warning('Invalid entry: "'.$entry.'" given in "'.self::class.'"!');
        }
        return false;
    }

    public function saveAll(){
        $this->dataConfig->save();
    }
}
