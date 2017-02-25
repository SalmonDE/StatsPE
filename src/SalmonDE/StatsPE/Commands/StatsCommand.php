<?php
namespace SalmonDE\StatsPE\Commands;

class StatsCommand extends \pocketmine\command\PluginCommand
{

    public function __construct(\SalmonDE\StatsPE\Base $owner){
        parent::__construct('statspe', $owner);
        $this->setPermission('statspe.cmd.stats');
        $this->setDescription('Shows the Stats of a player');
        $this->setUsage('/stats [player]');
    }

    public function execute(\pocketmine\command\CommandSender $sender, $label, array $args){
        
    }
}
