<?php
namespace SalmonDE\StatsPE;

use pocketmine\utils\Config;
use SalmonDE\StatsPE\Providers\Entry;

class Base extends \pocketmine\plugin\PluginBase
{

    private static $instance = null;
    private $provider = null;
    private $messages = [];

    public static function getInstance() : Base{
        return self::$instance;
    }

    public function onEnable(){
        self::$instance = $this;
        $this->saveResource('config.yml');
        $this->saveResource('messages.yml');
        $this->initialize();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->runUpdateManager();
    }

    public function onDisable(){
        $this->provider->saveAll();
    }

    private function initialize(){
        switch(strtolower($this->getConfig()->get('Provider'))){
            case 'json':
                $this->provider = new Providers\JSONProvider($this->getDataFolder().'players.json');
                break;
            case 'mysql':
                $this->provider = new Providers\MySQLProvider(($c = $this->getConfig())->get('host'), $c->get('username'), $c->get('password'), $c->get('database'));
        }
        $msgConfig = new Config($this->getDataFolder().'messages.yml', Config::YAML);
        $this->messages = $msgConfig->getAll();
        $this->registerDefaultEntries();
        $this->registerCommands();
    }

    private function registerDefaultEntries(){
        foreach($this->getConfig()->get('Stats') as $statistic => $enabled){
            if($enabled){
                switch($statistic){
                    case 'ClientID':
                        $default = 'undefined';
                        $expectedType = Entry::STRING;
                        $save = true;
                        break;

                    case 'LastIP':
                        $default = 'undefined';
                        $expectedType = Entry::STRING;
                        $save = true;
                        break;

                    case 'UUID':
                        $default = 'undefined';
                        $expectedType = Entry::STRING;
                        $save = true;
                        break;

                    case 'XBoxAuthenticated':
                        $default = false;
                        $expectedType = Entry::BOOL;
                        $save = false; //Not yet
                        break;

                    case 'OnlineTime':
                        $default = 0;
                        $expectedType = Entry::INT;
                        $save = true;
                        break;

                    case 'FirstJoin':
                        $default = 0;
                        $expectedType = Entry::INT;
                        $save = false;
                        break;

                    case 'LastJoin':
                        $default = 0;
                        $expectedType = Entry::INT;
                        $save = false;
                        break;

                    case 'K/D':
                        $default = 0.0;
                        $expectedType = Entry::FLOAT;
                        $save = false;
                        break;

                    case 'JoinCount':
                        $default = 1;
                        $expectedType = Entry::INT;
                        $save = true;
                        break;

                    case 'KillCount':
                        $default = 0;
                        $expectedType = Entry::INT;
                        $save = true;
                        break;

                    case 'DeathCount':
                        $default = 0;
                        $expectedType = Entry::INT;
                        $save = true;
                        break;

                    case 'BlockBreakCount':
                        $default = 0;
                        $expectedType = Entry::INT;
                        $save = true;
                        break;

                    case 'BlockPlaceCount':
                        $default = 0;
                        $expectedType = Entry::INT;
                        $save = true;
                        break;

                    case 'ChatCount':
                        $default = 0;
                        $expectedType = Entry::INT;
                        $save = true;
                        break;

                    case 'ItemConsumeCount':
                        $default = 0;
                        $expectedType = Entry::INT;
                        $save = true;
                        break;

                    case 'ItemCraftCount':
                        $default = 0;
                        $expectedType = Entry::INT;
                        $save = true;
                        break;

                    case 'ItemDropCount':
                        $default = 0;
                        $expectedType = Entry::INT;
                        $save = true;
                }
                $this->provider->addEntry(new Entry($statistic, $default, $expectedType, $save));
            }
        }
        $this->provider->addEntry(new Entry('RealName', 'undefined', Entry::STRING, true));
    }

    private function registerCommands(){
        $this->getServer()->getCommandMap()->register('StatsPE', new Commands\StatsCommand($this));
    }

    public function getDataProvider() : Providers\DataProvider{
        return $this->provider;
    }

    public function setDataProvider(Providers\DataProvider $provider){
        $this->provider = $provider;
    }

    public function getMessage(string $k){
        return $this->messages[$k];
    }

    public function runUpdateManager(){
        \SalmonDE\Updater\UpdateManager::getNew($this->getFile(), $this, $this->getConfig()->get('Auto-Update'))->start();
    }
}
