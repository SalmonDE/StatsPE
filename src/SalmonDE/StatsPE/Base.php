<?php
namespace SalmonDE\StatsPE;

use pocketmine\utils\Config;

class Base extends \pocketmine\plugin\PluginBase
{

    private static $instance = null;
    private $provider = null;
    private $messages = [];

    public function onEnable(){
        self::$instance = $this;
        $this->saveResource('config.yml');
        $this->saveResource('messages.yml');
        $this->initialize();
        $this->getServer()->getPluginManager()->registerEvents(new Listener(), $this);
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
    }

    public function getInstance() : Base{
        return self::$instance;
    }

    public function getDataProvider() : string{
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
