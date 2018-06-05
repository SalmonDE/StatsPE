<?php
declare(strict_types = 1);

namespace SalmonDE\StatsPE;

use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use SalmonDE\StatsPE\Commands\StatsCmd;
use SalmonDE\StatsPE\Commands\StatsPECmd;
use SalmonDE\StatsPE\DataProviders\DataProvider;
use SalmonDE\StatsPE\DataProviders\JSONProvider;
use SalmonDE\StatsPE\DataProviders\MySQLProvider;
use SalmonDE\StatsPE\Entries\Entry;
use SalmonDE\StatsPE\Entries\EntryManager;
use SalmonDE\StatsPE\FloatingTexts\FloatingTextManager;
use SalmonDE\StatsPE\Tasks\SaveTask;

class StatsBase extends PluginBase {

    /** @var JSONProvider|MySQLProvider */
    private $provider = null;

    /** @var string[] */
    private $messages = [];

    /** @var EventListener */
    private $listener = null; // Needed because of the OnlineTime hack
    /** @var FloatingTextManager */
    private $floatingTextManager = null;

    private static $entryManager;

    /**
     * @return void
     */
    public function onEnable(): void{
        $this->saveResource('config.yml');
        $this->saveResource('messages.yml');

        if($this->isEnabled()){

            if(!file_exists($this->getDataFolder().'messages.yml')){
                if($this->getResource($lang = ($this->getConfig()->get('Language').'.yml')) === null){
                    $lang = 'English.yml';
                }
                $this->saveResource($lang);
                rename($this->getDataFolder().$lang, $this->getDataFolder().'messages.yml');
            }

            $msgConfig = new Config($this->getDataFolder().'messages.yml', Config::YAML);
            $this->messages = $msgConfig->getAll();

            self::$entryManager = new EntryManager($this);

            $this->registerEntries();
            $this->initializeProvider();
            $this->registerCommands();

            if(($i = $this->getConfig()->getNested('JSON.saveInterval')) >= 1){
                $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new SaveTask($this), $i *= 1200, $i); // one minute in ticks (60 * 20)
            }else{
                $this->getLogger()->warning('The save interval is lower than 1 minute! Please make sure to always properly shutdown the server in order to prevent data loss!');
            }

            $this->floatingTextManager = $this->floatingTextManager ?? new FloatingTextManager($this);

            $this->getServer()->getPluginManager()->registerEvents($this->listener = new EventListener(), $this);
        }
    }

    /**
     * @return void
     */
    public function onDisable(): void{
        if(!$this->getServer()->isRunning()){
            foreach($this->getServer()->getOnlinePlayers() as $player){
                $this->listener->onQuit(new PlayerQuitEvent($player, '', '')); // Hacky, but prevents not saving online time of players on shutdown
            }
        }

        if(($this->provider ?? null) instanceof DataProvider){
            $this->provider->saveAll();
        }

        $this->listener = null;
    }

    /**
     * @return void
     */
    private function initializeProvider(): void{
        if($this->provider instanceof DataProvider){
            return;
        }

        switch($p = $this->getConfig()->get('Provider')){
            case 'json':
                $this->getLogger()->info('Selecting JSON data provider ...');

                $this->provider = new JSONProvider($this->getDataFolder().'players.json');
                break;

            case 'mysql':
                $this->getLogger()->info('Selecting MySQL data provider ...');

                $c = $this->getConfig();
                $this->provider = new MySQLProvider($c->getNested('MySQL.host'), $c->getNested('MySQL.username'), $c->getNested('MySQL.password'), $c->getNested('MySQL.database'), $c->getNested('MySQL.cacheLimit'));
                break;

            default:
                $this->getLogger()->warning('Unknown provider: "'.$p.'", selecting JSON data provider...');

                $this->provider = new JSONProvider($this->getDataFolder().'players.json');
        }
    }

    public static function getEntryManager(): EntryManager{
        return self::$entryManager;
    }

    /**
     * @return void
     */
    private function registerEntries(): void{
        $this->provider->addEntry(new Entry('Username', 'undefined', Entry::STRING, true));
        foreach($this->getConfig()->get('Stats') as $statistic => $enabled){
            if($enabled){
                $unsigned = false;

                switch($statistic){
                    case 'Online':
                        $default = false;
                        $expectedType = Entry::TYPE_BOOL;
                        $save = true;
                        break;

                    case 'ClientID':
                        $default = 'undefined';
                        $expectedType = Entry::TYPE_STRING;
                        $save = true;
                        break;

                    case 'LastIP':
                        $default = 'undefined';
                        $expectedType = Entry::TYPE_STRING;
                        $save = true;
                        break;

                    case 'UUID':
                        $default = 'undefined';
                        $expectedType = Entry::TYPE_STRING;
                        $save = true;
                        break;

                    case 'XBoxAuthenticated':
                        $default = false;
                        $expectedType = Entry::TYPE_BOOL;
                        $save = false; //Not yet
                        break;

                    case 'OnlineTime':
                        $default = 0;
                        $expectedType = Entry::TYPE_INT;
                        $save = true;
                        $unsigned = true;
                        break;

                    case 'FirstJoin':
                        $default = 0.0;
                        $expectedType = Entry::TYPE_FLOAT;
                        $save = true;
                        break;

                    case 'LastJoin':
                        $default = 0.0;
                        $expectedType = Entry::TYPE_FLOAT;
                        $save = true;
                        break;

                    case 'K/D':
                        $default = 0.0;
                        $expectedType = Entry::TYPE_FLOAT;
                        $save = false;
                        break;

                    case 'JoinCount':
                        $default = 1;
                        $expectedType = Entry::TYPE_INT;
                        $save = true;
                        $unsigned = true;
                        break;

                    case 'KillCount':
                        $default = 0;
                        $expectedType = Entry::TYPE_INT;
                        $save = true;
                        $unsigned = true;
                        break;

                    case 'DeathCount':
                        $default = 0;
                        $expectedType = Entry::TYPE_INT;
                        $save = true;
                        $unsigned = true;
                        break;

                    case 'BlockBreakCount':
                        $default = 0;
                        $expectedType = Entry::TYPE_INT;
                        $save = true;
                        $unsigned = true;
                        break;

                    case 'BlockPlaceCount':
                        $default = 0;
                        $expectedType = Entry::TYPE_INT;
                        $save = true;
                        $unsigned = true;
                        break;

                    case 'ChatCount':
                        $default = 0;
                        $expectedType = Entry::TYPE_INT;
                        $save = true;
                        $unsigned = true;
                        break;

                    case 'ItemConsumeCount':
                        $default = 0;
                        $expectedType = Entry::TYPE_INT;
                        $save = true;
                        $unsigned = true;
                        break;

                    case 'ItemCraftCount':
                        $default = 0;
                        $expectedType = Entry::TYPE_INT;
                        $save = true;
                        $unsigned = true;
                        break;

                    case 'ItemDropCount':
                        $default = 0;
                        $expectedType = Entry::TYPE_INT;
                        $save = true;
                        $unsigned = true;
                }
                $this->provider->addEntry(new Entry($statistic, $default, $expectedType, $save, $unsigned));
            }
        }
        if($this->getDataProvider()->entryExists('K/D')){
            if(!$this->getDataProvider()->entryExists('KillCount') || !$this->getDataProvider()->entryExists('DeathCount')){
                $this->getLogger()->warning('Disabled K/D entry due to error prevention! Did you enable KillCount and DeathCount in the config?');
                $this->getDataProvider()->removeEntry($this->getDataProvider()->getEntry('K/D'));
            }
        }
    }

    /**
     * @return void
     */
    private function registerCommands(): void{
        $this->getServer()->getCommandMap()->register('statspe', new StatsCmd($this));
        $this->getServer()->getCommandMap()->register('statspe', new StatsPECmd($this));
    }

    /**
     * @return DataProvider
     */
    public function getDataProvider(): DataProvider{
        return $this->provider;
    }

    /**
     * @param DataProvider $provider
     *
     * @return void
     */
    public function setDataProvider(DataProvider $provider): void{
        $this->provider->saveAll();

        $this->provider = $provider;
    }

    /**
     * @return FloatingTextManager
     */
    public function getFloatingTextManager(): FloatingTextManager{
        return $this->floatingTextManager;
    }

    /**
     * @param string $k
     *
     * @return string
     */
    public function getMessage(string $k): ?string{
        $keys = explode('.', $k);
        $message = $this->messages['lines'];

        foreach($keys as $k){
            $message = $message[$k];
        }

        return $message;
    }

}
