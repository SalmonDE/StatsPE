<?php
namespace SalmonDE\StatsPE\Providers;

class MySQLProvider implements DataProvider //ToDo if x changes -> save
{

    private $entries = [];

    public function __construct(bool $strict, $host, $username, $password, $db){
        $this->initialize(['host' => $host, 'username' => $username, 'password' => $password, 'db' => $db]);
    }

    public function initialize(array $data, bool $strict){

    }

    public function addPlayer(\pocketmine\Player $player){

    }

    public function getData(string $player, string $entry){

    }

    public function getAllData() : array{

    }

    public function saveData(string $player, string $entry, $value){

    }

    public function addEntry(string $entry, int $expectedType){

    }

    public function removeEntry(string $entry){

    }

    public function getEntries() : array{
        return $this->entries;
    }

    public function validEntry(string $entry) : bool{
        if(isset($this->entries[$entry])){
            return true;
        }
        Base::getInstance()->getLogger()->warning('Invalid entry: "'.$entry.'" given in "'.self::class.'"!');
        return false;
    }

    public function saveAll(){}
}
