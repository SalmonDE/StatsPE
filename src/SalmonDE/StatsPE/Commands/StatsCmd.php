<?php
namespace SalmonDE\StatsPE\Commands;

class StatsCmd extends \pocketmine\command\PluginCommand
{

    public function __construct(\SalmonDE\StatsPE\Base $owner){
        parent::__construct('stats', $owner);
        $this->setPermission('statspe.cmd.stats');
        $this->setDescription($owner->getMessage('commands.stats.description'));
        $this->setUsage($owner->getMessage('commands.stats.usage'));
        $this->setExecutor(new StatsCmdExecutor());
    }
}
