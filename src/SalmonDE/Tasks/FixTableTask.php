<?php
namespace SalmonDE\Tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;

class FixTableTask extends AsyncTask
{
    public function __construct($owner, $sender){
        $this->lang = $owner->getMessages();
        $this->mysql = $owner->getConfig()->get('MySQL');
        $this->provider = strtolower($owner->getConfig()->get('Provider'));
        if($sender->getName() == 'CONSOLE'){
            $this->sender = $sender;
        }else{
            $this->sender = $sender->getName();
        }
        $this->yes = $owner->getConfig()->get('Yes');
        $this->no =$owner->getConfig()->get('No');
    }

    public function onRun(){
        if($this->provider == 'mysql'){
            $connection = @mysqli_connect($this->mysql['host'], $this->mysql['user'], $this->mysql['password'], $this->mysql['database']);
            if($connection){
                $stats = [
                    '1' =>[
                        'Name' => 'PlayerName',
                        'Datatype' => 'VARCHAR(16)',
                        'Extra' => 'NOT NULL UNIQUE'
                    ],
                    '2' => [
                        'Name' => 'Online',
                        'Datatype' => 'VARCHAR10',
                        'Extra' => "NOT NULL DEFAULT '".$this->yes."'"
                    ],
                    '3' => [
                        'Name' => 'ClientID',
                        'Datatype' => 'VARCHAR(30)',
                        'Extra' => 'NOT NULL'
                    ],
                    '4' => [
                        'Name' => 'UUID',
                        'Datatype' => 'VARCHAR(30)',
                        'Extra' => 'NOT NULL'
                    ],
                    '5' => [
                        'Name' => 'XBoxAuthenticated',
                        'Datatype' => 'VARCHAR(10)',
                        'Extra' => "NOT NULL DEFAULT '".$this->no."'"
                    ],
                    '6' => [
                        'Name' => 'LastIP',
                        'Datatype' => 'VARCHAR(15)',
                        'Extra' => 'NOT NULL'
                    ],
                    '7' => [
                        'Name' => 'FirstJoin',
                        'Datatype' => 'VARCHAR(30)',
                        'Extra' => 'NOT NULL'
                    ],
                    '8' => [
                        'Name' => 'LastJoin',
                        'Datatype' => 'VARCHAR(30)',
                        'Extra' => 'NOT NULL'
                    ],
                    '9' => [
                        'Name' => 'JoinCount',
                        'Datatype' => 'INT(255)',
                        'Extra' => "UNSIGNED DEFAULT 0"
                    ],
                    '10' => [
                        'Name' => 'KillCount',
                        'Datatype' => 'INT(255)',
                        'Extra' => "UNSIGNED DEFAULT 0"
                    ],
                    '11' => [
                        'Name' => 'DeathCount',
                        'Datatype' => 'INT(255)',
                        'Extra' => "UNSIGNED DEFAULT 0"
                    ],
                    '12' => [
                        'Name' => 'KickCount',
                        'Datatype' => 'INT(255)',
                        'Extra' => "UNSIGNED DEFAULT 0"
                    ],
                    '13' => [
                        'Name' => 'BlocksBreaked',
                        'Datatype' => 'INT(255)',
                        'Extra' => "UNSIGNED DEFAULT 0"
                    ],
                    '14' => [
                        'Name' => 'BlocksPlaced',
                        'Datatype' => 'INT(255)',
                        'Extra' => "UNSIGNED DEFAULT 0"
                    ],
                    '15' => [
                        'Name' => 'ChatMessages',
                        'Datatype' => 'INT(255)',
                        'Extra' => "UNSIGNED DEFAULT 0"
                    ],
                    '16' => [
                        'Name' => 'FishCount',
                        'Datatype' => 'INT(255)',
                        'Extra' => "UNSIGNED DEFAULT 0"
                    ],
                    '17' => [
                        'Name' => 'EnterBedCount',
                        'Datatype' => 'INT(255)',
                        'Extra' => "UNSIGNED DEFAULT 0"
                    ],
                    '18' => [
                        'Name' => 'EatCount',
                        'Datatype' => 'INT(255)',
                        'Extra' => "UNSIGNED DEFAULT 0"
                    ],
                    '19' => [
                        'Name' => 'CraftCount',
                        'Datatype' => 'INT(255)',
                        'Extra' => "UNSIGNED DEFAULT 0"
                    ],
                    '20' => [
                        'Name' => 'DroppedItems',
                        'Datatype' => 'INT(255)',
                        'Extra' => "UNSIGNED DEFAULT 0"
                    ]
                ];
                foreach($stats as $column){
                    $name = $column['Name'];
                    $datatype = $column['Datatype'];
                    $extra = $column['Extra'];
                    mysqli_query($connection, "ALTER TABLE Stats ADD $name $datatype $extra");
                    mysqli_query($connection, "ALTER TABLE Stats CHANGE '$name' '$name' $datatype SET $extra");
                }
            }else{
                $this->setResult($this->lang['MySQL']['ConnectFailure'].mysqli_error($connection));
            }
        }else{
            $this->setResult($this->lang['ProviderInvalid']);
        }
    }

    public function onCompletion(Server $server){
        if($this->getResult()){
            if(is_string($this->sender)){
                $server->getPlayerExact($this->sender)->sendMessage(TF::RED.$this->getResult());
            }else{
                $this->sender->sendMessage(TF::RED.$this->getResult());
            }
        }else{
            if(is_string($this->sender)){
                $server->getPlayerExact($this->sender)->sendMessage(TF::GREEN.$this->lang['MySQL']['CommandFixTableDone']);
            }else{
                $this->sender->sendMessage(TF::GREEN.$this->lang['MySQL']['CommandFixTableDone']);
            }
        }
    }
}
