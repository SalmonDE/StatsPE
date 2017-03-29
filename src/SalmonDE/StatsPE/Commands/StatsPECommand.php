<?php
namespace SalmonDE\StatsPE\Commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use SalmonDE\StatsPE\Base;

class StatsPECommand extends \pocketmine\command\PluginCommand
{

    private $dataProvider = null;

    public function __construct(\SalmonDE\StatsPE\Base $owner){
        parent::__construct('statspe', $owner);
        $this->setPermission('statspe.cmd.statspe');
        $this->setDescription($owner->getMessage('commands.statspe.description'));
        $this->setUsage($owner->getMessage('commands.statspe.usage'));
    }

    public function execute(\pocketmine\command\CommandSender $sender, $label, array $args){
        if(isset($args[0])){
            switch($args[0]){
                case 'floatingtexts':
                    break;
            }
        }else{
            $messages = [
                Base::getInstance()->getMessage('commands.statspe.header'),
                Base::getInstance()->getMessage('commands.statspe.author'),
                Base::getInstance()->getMessage('commands.statspe.api'),
                Base::getInstance()->getMessage('commands.statspe.datarecords'),
                Base::getInstance()->getMessage('commands.statspe.entries')
            ];

            foreach(Base::getInstance()->getDataProvider()->getEntries() as $entry){
                $entries[] = $entry->getName();
            }

            $values = [
                '{full_name}' => Base::getInstance()->getDescription()->getFullName(),
                '{author}' => implode(', ', Base::getInstance()->getDescription()->getAuthors()),
                '{apis}' => implode(', ', Base::getInstance()->getDescription()->getCompatibleApis()),
                '{records_amount}' => Base::getInstance()->getDataProvider()->countDataRecords(),
                '{entries}' => implode('; ', $entries)
            ];

            $sender->sendMessage(str_replace(array_keys($values), array_values($values), implode(TF::RESET."\n", $messages)));
        }
    }
}
