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
                    '1' => [
                        'Name' => 'Online',
                        'Default' => $this->yes
                    ],
                    '2' => [
                        'Name' => 'XBoxAuthenticated',
                        'Default' => $this->no
                    ]
                ];
                foreach($stats as $column){
                    $name = $column['Name'];
                    $default = $column['Default'];
                    mysqli_query($connection, "ALTER TABLE Stats ADD $name VARCHAR(10) NOT NULL DEFAULT '$default'");
                    mysqli_query($connection, "ALTER TABLE Stats CHANGE '$name' '$name' VARCHAR(10) SET DEFAULT '$default'");
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
