<?php
namespace SalmonDE\StatsPE\DataProviders;

use pocketmine\Player;
use SalmonDE\StatsPE\Entries\Entry;
use SalmonDE\StatsPE\StatsBase;
use SalmonDE\StatsPE\Events\DataReceiveEvent;
use SalmonDE\StatsPE\Events\DataSaveEvent;
use SalmonDE\StatsPE\Tasks\SaveToDbTask;
use SalmonDE\StatsPE\Utils;

class MySQLProvider extends DataProvider {

    private $owner;

    private $entries = [];
    private $db = null;

    private $changes = [
        'amount' => 0,
        'data' => []
    ];

    private $cacheLimit = 0;

    public function __construct(StatsBase $owner, $host, $username, $pw, $db, $cacheLimit){
        $this->owner = $owner;
        $this->initialize(['host' => $host, 'username' => $username, 'pw' => $pw,  'db' => $db, 'cacheLimit' => $cacheLimit]);
    }

    public function initialize(array $data){
        $this->cacheLimit = $data['cacheLimit'];

        $host = explode(':', $data['host']);
        $data['host'] = $host[0];
        $data['port'] = isset($host[1]) ? $host[1] : 3306;

        @$this->db = new \mysqli($data['host'], $data['username'], $data['pw'], $data['db'], $data['port']);

        if($this->db->connect_error){
            $this->owner->getLogger()->critical('Error while connecting to the MySQL server: ('.$this->db->connect_errno.')');
            $this->owner->getLogger()->critical(trim($this->db->connect_error));

            $this->owner->getServer()->getPluginManager()->disablePlugin($this->owner);
            return false;
        }

        $this->owner->getLogger()->notice('Successfully connected to the MySQL server!');

        $this->prepareTable();
        return true;
    }

    public function prepareTable(){
        $columns = ['Username VARCHAR(16) UNIQUE NOT NULL'];
        foreach($this->entries as $entry){
            if($entry->storesData() && $entry->getName() !== 'Username'){
                $type = Utils::getMySQLDatatype($entry->getExpectedType());
                $columns[] = $this->db->real_escape_string($entry->getName()).' '.$type.($entry->isUnsigned() ? ' UNSIGNED ' : ' ').'NOT NULL DEFAULT '.(is_string($value = Utils::convertValueSave($entry, $entry->getDefault())) ? "'".$this->db->real_escape_string($value)."'" : $value);
            }
        }

        $this->queryDb('CREATE TABLE IF NOT EXISTS StatsPE( '.implode(', ', $columns).' ) COLLATE utf8_general_ci', []);

        // Check if all entries have their columns
        $existingColumns = $this->queryDb('DESCRIBE StatsPE', [])->fetch_all();
        $limit = count($existingColumns);
        for($k = 0; $k < $limit; $k++){
            $existingColumns[$k] = $existingColumns[$k][0];
        }

        $missingColumns = [];
        foreach($this->entries as $entry){
            if(array_search($entry->getName(), $existingColumns) === false && $entry->storesData()){
                $missingColumns[] = $entry;
            }
        }

        if(count($missingColumns) > 0){
            foreach($missingColumns as $column){
                $this->queryDb('ALTER TABLE StatsPE ADD '.$this->db->real_escape_string($column->getName()).' '.Utils::getMySQLDatatype($column->getExpectedType()).' NOT NULL DEFAULT ?', [
                    $this->db->real_escape_string(Utils::convertValueSave($column, $column->getDefault()))]);
            }
        }
    }

    public function addPlayer(Player $player){
        $this->queryDb('INSERT INTO StatsPE (Username) VALUES ( ? )', [$player->getName()]);
    }

    public function getData(string $playerName, Entry $entry){
        if($this->entryExists($entry->getName())){
            if(!$entry->storesData()){
                return;
            }
            $value = $this->queryDb('SELECT '.$this->db->real_escape_string($entry->getName()).' FROM StatsPE WHERE Username=?', [$playerName])->fetch_assoc()[$entry->getName()];
            $value = Utils::convertValueGet($entry, $value);

            $this->applyChanges($playerName, $entry, $value);

            $event = new DataReceiveEvent($this->owner, $value, $playerName, $entry);
            $this->owner->getServer()->getPluginManager()->callEvent($event);
            return $event->getData();
        }
    }

    public function getDataWhere(Entry $needleEntry, $needle, array $wantedEntries){
        if($this->entryExists($needleEntry->getName()) && $needleEntry->storesData()){
            if($wantedEntries === []){
                return [];
            }

            foreach($wantedEntries as $entry){
                if($entry->storesData()){
                    $entryList[] = $this->db->real_escape_string($entry->getName());
                }else{
                    $missingEntries[] = $entry->getName();
                }
            }

            $query = 'SELECT '.implode(', ', $entryList).' FROM StatsPE WHERE '.$this->db->real_escape_string($needleEntry->getName()).' = ?';

            $query = $this->queryDb($query, [$needle]);

            while($row = $query->fetch_assoc()){
                $resultData[array_shift($row)] = $row;
            }

            foreach($resultData as $player => $playerData){
                foreach($playerData as $entryName => $value){
                    $resultData[$player][$entryName] = Utils::convertValueGet($wantedEntries[$entryName], $value);
                }
            }

            if(isset($missingEntries)){

                foreach($resultData as $player => $playerData){
                    foreach($missingEntries as $entryName){
                        $resultData[$player][$entryName] = null;
                    }
                }
            }

            return $resultData;
        }
    }

    public function getAllData(string $player = null){
        $data = [];

        if($player !== null){
            $query = $this->queryDb('SELECT * FROM StatsPE WHERE Username=?', [$player]);

            while($row = $query->fetch_assoc()){
                $data[array_shift($row)] = $row;
            }

            if($data === []){
                return;
            }else{
                $name = array_keys($data)[0];
                $data[$name]['Username'] = $name;

                foreach($data[$name] as $entryName => $value){
                    if($this->entryExists($entryName)){
                        $value = Utils::convertValueGet($this->getEntry($entryName), $value);
                        $this->applyChanges($name, $this->getEntry($entryName), $value);
                    }

                    $data[$entryName] = $value;
                }
                unset($data[$name]);

                $event = new DataReceiveEvent($this->owner, $data);
                $this->owner->getServer()->getPluginManager()->callEvent($event);
                return $event->getData();
            }
        }

        $query = $this->queryDb('SELECT * FROM StatsPE', []);

        while($row = $query->fetch_assoc()){
            $data[array_shift($row)] = $row;
        }

        foreach($data as $playerName => $playerData){
            foreach($playerData as $entryName => $value){
                if($this->entryExists($entryName)){
                    $value = Utils::convertValueGet($this->getEntry($entryName), $value);
                    $this->applyChanges($playerName, $this->getEntry($entryName), $value);
                }

                $data[$playerName][$entryName] = $value;
            }
        }

        $event = new DataReceiveEvent($this->owner, $data);
        $this->owner->getServer()->getPluginManager()->callEvent($event);
        return $event->getData();
    }

    private function applyChanges(string $playerName, Entry $entry, &$value){
        $playerName = $this->db->real_escape_string($playerName);
        $entryName = $this->db->real_escape_string($entry->getName());
        $value = $this->db->real_escape_string($value);

        if(isset($this->changes['data'][$playerName][$entryName])){
            if(($data = $this->changes['data'][$playerName][$entryName])['isIncrement']){
                $value += $data['value'];
            }else{
                $value = $data['value'];
            }
        }
    }

    public function saveData(string $playerName, Entry $entry, $value){
        if($this->entryExists($entry->getName()) && $entry->storesData()){
            if($entry->isValidType($value)){
                $event = new DataSaveEvent($this->owner, $value, $playerName, $entry);
                $this->owner->getServer()->getPluginManager()->callEvent($event);

                if(!$event->isCancelled()){
                    $this->addChange($playerName, $entry, $value, false);
                }
            }else{
                $this->owner->getLogger()->error($msg = 'Unexpected datatype "'.gettype($value).'" given for entry "'.$entry->getName().'" in "'.self::class.'" by "'.__FUNCTION__.'"!');
            }
        }
    }

    public function incrementValue(string $playerName, Entry $entry, int $int = 1){
        if($this->entryExists($entry->getName()) && $entry->storesData() && $entry->getExpectedType() === Entry::TYPE_INT){

            $event = new DataSaveEvent($this->owner, $int, $playerName, $entry);
            $this->owner->getServer()->getPluginManager()->callEvent($event);

            if(!$event->isCancelled()){
                $this->addChange($playerName, $entry, $int, true);
            }
        }
    }

    private function addChange(string $playerName, Entry $entry, $value, bool $isIncrement){
        $playerName = $this->db->real_escape_string($playerName);
        $entryName = $this->db->real_escape_string($entry->getName());

        if($isIncrement && isset($this->changes['data'][$playerName][$entryName]['value'])){
            $this->changes['data'][$playerName][$entryName]['value'] += $value;
        }else{
            $this->changes['data'][$playerName][$entryName] = ['value' => $value,
                'isIncrement' => $isIncrement];
        }

        if(++$this->changes['amount'] > $this->cacheLimit){
            $this->saveAll();
        }
    }

    public function countDataRecords(): int{
        return (int) $this->queryDb('SELECT COUNT(*) FROM StatsPE', [])->fetch_assoc()['COUNT(*)'];
    }

    public function saveAll(){
        $query = '';
        foreach($this->changes['data'] as $playerName => $changedData){
            foreach($changedData as $entryName => $data){
                if($data['isIncrement']){
                    $query .= 'UPDATE StatsPE SET '.$entryName.' = '.$entryName.' + '.$data['value'].' WHERE Username='."'".$playerName."'".'; ';
                }else{
                    $data['value'] = Utils::convertValueSave($this->getEntry($entryName), $data['value']);
                    $query .= 'UPDATE StatsPE SET '.$entryName.' = '.(is_string($data['value']) ? "'".$this->db->real_escape_string($data['value'])."'" : $data['value']).' WHERE Username='."'".$playerName."'".'; ';
                }
            }
        }

        $this->owner->getServer()->getScheduler()->scheduleAsyncTask(new SaveToDbTask($this->owner->getConfig()->get('MySQL'), $this->changes['data'], $this));

        $this->changes['amount'] = 0;
        $this->changes['data'] = [];
    }

    private function queryDb(string $query, array $values){ // reconnecting?
        $valueTypes = '';
        foreach($values as $value){
            $valueTypes .= is_numeric($value) ? (is_float($value) ? 'd' : 'i') : 's';
        }

        @$statement = $this->db->prepare($query);
        if($statement === false){
            if(!@$this->db->ping()){
                if(!$this->initialize($this->owner->getConfig()->get('MySQL'))){
                    $this->owner->getServer()->getPluginManager()->disablePlugin($this->owner);
                }else{
                    $statement = $this->db->prepare($query);
                }
                return false;
            }else{
                $this->owner->getLogger()->error('Syntax error in query to database: "'.$query.'"');
                return false;
            }
        }

        if(strpos($query, '?') !== false){
            $statement->bind_param($valueTypes, ...$values);
        }

        if($statement->execute()){
            return $statement->get_result();
        }else{
            $this->owner->getLogger()->debug('Query: "'.$query.'"');
            $this->owner->getLogger()->error('Query to the database failed: ('.$this->db->errno.')');
            $this->owner->getLogger()->error($this->db->error);
            return false;
        }
    }

}