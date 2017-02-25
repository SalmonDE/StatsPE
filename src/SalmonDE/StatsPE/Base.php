<?php
namespace SalmonDE\StatsPE;

class Base extends \pocketmine\plugin\PluginBase
{

    private static $instance = null;
    private $provider = 'json';

    public function onEnable(){
        self::$instance = $this;
        $this->saveResource('config.yml');
        $this->initialize();
        $this->getServer()->getPluginManager()->registerEvents(new Listener(), $this);
        $this->runUpdateManager();
    }

    private function initialize(){
        switch(strtolower($this->getConfig()->get('Provider'))){
            case 'json':
                $this->provider = new Providers\JSONProvider($this->getDataFolder().'players.json');
                break;
            case 'mysql':
                $this->provider = new Providers\MySQLProvider(($c = $this->getConfig())->get('host'), $c->get('username'), $c->get('password'), $c->get('database'));
        }
    }

    public function getInstance() : Base{
        return self::$instance;
    }

    public function getDataProvider() : string{
        return $this->provider;
    }

    public function runUpdateManager(){
        \SalmonDE\Updater\UpdateManager::getNew($this->getFile(), $this, $this->getConfig()->get('Auto-Update'))->start();
    }
}
