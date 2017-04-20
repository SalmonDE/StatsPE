<?php
namespace SalmonDE\StatsPE\Providers;

use SalmonDE\StatsPE\Base;
use SalmonDE\StatsPE\Utils;

class MySQLProvider implements DataProvider
{

    private $entries = [];
    private $db = null;

    private $changes = [];

    public function __construct($host, $username, $pw, $db){
        $this->initialize(['host' => $host, 'username' => $username, 'pw' => $pw, 'db' => $db]);
    }

    public function initialize(array $data){
        $host = explode(':', $data['host']);
        $data['host'] = $host[0];
        $data['port'] = isset($host[1]) ? $host[1] : 3306;

        @$this->db = new \mysqli($data['host'], $data['username'], $data['pw'], $data['db'], $data['port']);

        if($this->db->connect_error){
            Base::getInstance()->getLogger()->critical('Error while connecting to the MySQL server: ('.$this->db->connect_errno.')');
            Base::getInstance()->getLogger()->critical(trim($this->db->connect_error));

            Base::getInstance()->getServer()->getPluginManager()->disablePlugin(Base::getInstance());
            return false;
        }

        Base::getInstance()->getLogger()->notice('Successfully connected to the MySQL server!');
        return true;
    }

    public function getName() : string{
        return 'MySQLProvider';
    }

    public function prepareTable(){
        $columns = ['Username VARCHAR(16) UNIQUE NOT NULL'];
        foreach($this->entries as $entry){
            if($entry->shouldSave() && $entry->getName() !== 'Username'){
                $type = Utils::getMySQLDatatype($entry->getExpectedType());
                $columns[] = $this->db->real_escape_string($entry->getName()).' '.$type.' NOT NULL DEFAULT '.(is_string($value = $this->db->real_escape_string(Utils::convertValueSave($entry, $entry->getDefault()))) ? "'$value'" : $value);
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
            if(array_search($entry->getName(), $existingColumns) === false && $entry->shouldSave()){
                $missingColumns[] = $entry;
            }
        }
        if(count($missingColumns) > 0){
            foreach($missingColumns as $column){
                $this->queryDb('ALTER TABLE StatsPE ADD '.$this->db->real_escape_string($column->getName()).' '.Utils::getMySQLDatatype($column->getExpectedType()).' NOT NULL DEFAULT ?', [$this->db->real_escape_string(Utils::convertValueSave($entry, $entry->getDefault()))]);
            }
        }
    }

    public function addPlayer(\pocketmine\Player $player){
        $this->queryDb('INSERT INTO StatsPE (Username) VALUES ( ? )', [$player->getName()]);
    }

    public function getData(string $player, Entry $entry){
        if($this->entryExists($entry->getName())){
            if(!$entry->shouldSave()){
                return;
            }
            $v = $this->queryDb('SELECT '.$this->db->real_escape_string($entry->getName()).' FROM StatsPE WHERE Username=?', [$player])->fetch_assoc()[$entry->getName()];
            $v = Utils::convertValueGet($entry, $v);
            if($entry->isValidType($v)){
                return $v;
            }
            Base::getInstance()->getLogger()->error($msg = 'Unexpected datatype returned "'.gettype($v).'" for entry "'.$entry->getName().'" in "'.self::class.'" by "'.__FUNCTION__.'"!');
        }
    }

    public function getAllData(string $player = null){
        $data = [];

        if($player !== null){
            $query = $this->queryDb('SELECT * FROM StatsPE WHERE Username=?', [$player]);

            while ($row = $query->fetch_assoc()){
                $data[array_shift($row)] = $row;
            }

            if($data === []){
                return;
            }else{
                $name = array_keys($data)[0];
                $data[$name]['Username'] = $name;
                return $data[$name];
            }
        }

        $query = $this->queryDb('SELECT * FROM StatsPE', []);

        while ($row = $query->fetch_assoc()){
            $data[array_shift($row)] = $row;
        }
        return $data;
    }

    public function saveData(string $player, Entry $entry, $value){
        if($this->entryExists($entry->getName()) && $entry->shouldSave()){
            if($entry->isValidType($value)){
                $this->queryDb('UPDATE StatsPE SET '.$this->db->real_escape_string($entry->getName()).'=? WHERE Username=?', [$value, $player]);
            }else{
                Base::getInstance()->getLogger()->error($msg = 'Unexpected datatype "'.gettype($value).'" given for entry "'.$entry->getName().'" in "'.self::class.'" by "'.__FUNCTION__.'"!');
            }
        }
    }

    public function incrementValue(string $player, Entry $entry, int $int = 1){
        if($this->entryExists($entry->getName()) && $entry->shouldSave() && $entry->getExpectedType() === Entry::INT){
            $this->queryDb('UPDATE StatsPE SET '.($entryName = $this->db->real_escape_string($entry->getName())).' = '.$entryName.' + '.$int.' WHERE Username=?', [$player]);
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
        if($this->entryExists($entry->getName()) && $entry->getName() !== 'Username'){
            unset($this->entries[$entry->getName()]);
        }
    }

    public function getEntries() : array{
        return $this->entries;
    }

    public function getEntry(string $entry){
        if(isset($this->entries[$entry])){
            return $this->entries[$entry];
        }
    }

    public function entryExists(string $entry) : bool{
        return isset($this->entries[$entry]);
    }

    public function countDataRecords() : int{
        return (int) $this->queryDb('SELECT COUNT(*) FROM StatsPE', [])->fetch_assoc()['COUNT(*)'];
    }

    public function saveAll(){}

    private function queryDb(string $query, array $values){
        $valueTypes = '';
        foreach($values as $value){
            $valueTypes .= is_numeric($value) ? (is_float($value) ? 'd' : 'i') : 's';
        }

        @$statement = $this->db->prepare($query);
        if($statement === false){
            if(!@$this->db->ping()){
                if(!$this->initialize(Base::getInstance()->getConfig()->get('MySQL'))){
                    Base::getInstance()->getServer()->getPluginManager()->disablePlugin(Base::getInstance());
                }else{
                    $statement = $this->db->prepare($query);
                }
                return false;
            }else{
                Base::getInstance()->getLogger()->error('Syntax error in query to database: "'.$query.'"');
                return false;
            }
        }

        if(strpos($query, '?') !== false){
            $statement->bind_param($valueTypes, ...$values);
        }

        if($statement->execute()){
            return $statement->get_result();
        }else{
            Base::getInstance()->getLogger()->debug('Query: "'.$query.'"');
            Base::getInstance()->getLogger()->error('Query to the database failed: ('.$this->db->errno.')');
            Base::getInstance()->getLogger()->error($this->db->error);
            return false;
        }
    }
}
