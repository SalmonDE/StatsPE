<?php
namespace SalmonDE\StatsPE\Commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class StatsCommand extends \pocketmine\command\PluginCommand
{

    private $dataProvider = null;

    public function __construct(\SalmonDE\StatsPE\Base $owner){
        parent::__construct('stats', $owner);
        $this->setPermission('statspe.cmd.stats');
        $this->setDescription($owner->getMessage('commands.stats.description'));
        $this->setUsage($owner->getMessage('commands.stats.usage'));
        $this->dataProvider = $owner->getDataProvider();
    }

    public function execute(\pocketmine\command\CommandSender $sender, $label, array $args){
        if(!isset($args[0])){
            if(!$sender instanceof Player){
                return false;
            }
            $args[0] = $sender->getName();
        }

        if(is_array($data = $this->dataProvider->getAllData($args[0]))){
            $sender->sendMessage(str_replace('{player}', $data['Username'], $this->getPlugin()->getMessage('general.header')));
            foreach($this->dataProvider->getEntries() as $entry){
                if($sender->hasPermission('statspe.entry.'.$entry->getName())){
                    switch($entry->getName()){
                        case 'FirstJoin':
                            $p = $sender->getServer()->getOfflinePlayer($args[0]);
                            $value = $p->getFirstPlayed();
                            break;

                        case 'LastJoin':
                            $p = $sender->getServer()->getOfflinePlayer($args[0]);
                            $value = $p->getLastPlayed();
                            break;

                        case 'OnlineTime':
                            $seconds = $data['OnlineTime'];
                            if(($p = $sender->getServer()->getPlayerExact($data['Username'])) instanceof Player){
                                $seconds += round(time() - ($p->getLastPlayed() / 1000));
                            }

                            $value = \SalmonDE\StatsPE\Utils::getPeriodFromSeconds($seconds);
                            break;

                        case 'K/D':
                            $value = \SalmonDE\StatsPE\Utils::getKD($data['KillCount'], $data['DeathCount']);
                            break;

                        default:
                            $value = $data[$entry->getName()];
                    }
                    $sender->sendMessage(TF::AQUA.$entry->getName().': '.TF::GOLD.$value);
                }
            }
        }else{
            $sender->sendMessage(TF::RED.str_replace('{player}', $args[0], $this->getPlugin()->getMessage('commands.stats.notFound')));
            return true;
        }
    }
}
