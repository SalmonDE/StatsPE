<?php
namespace SalmonDE\StatsPE\Providers;

use SalmonDE\StatsPE\Base;

class MySQLProvider implements DataProvider //ToDo if x changes -> save
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
        }else{
            Base::getInstance()->getLogger()->notice('Successfully connected to mysql server!');
        }
    }

    public function addPlayer(\pocketmine\Player $player){

    }

    public function getData(string $player, Entry $entry){

    }

    public function getAllData(string $player = null) : array{

    }

    public function saveData(string $player, Entry $entry, $value){

    }

    public function addEntry(Entry $entry){

    }

    public function removeEntry(Entry $entry){

    }

    public function getEntries() : array{
        return $this->entries;
    }

    public function entryExists(string $entry) : bool{

    }

    public function saveAll(){
        if(!$this->db->connect_error){

        }
    }
}
