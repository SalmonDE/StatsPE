<?php
namespace SalmonDE\StatsPE\Commands;

class StatsPECmd extends \pocketmine\command\PluginCommand
{

    public function __construct(\SalmonDE\StatsPE\Base $owner){
        parent::__construct('statspe', $owner);
        $this->setPermission('statspe.cmd.statspe');
        $this->setDescription($owner->getMessage('commands.statspe.description'));
        $this->setUsage($owner->getMessage('commands.statspe.usage'));
        $this->setExecutor(new StatsPECmdExecutor());
    }
}
