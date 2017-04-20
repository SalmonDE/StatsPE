<?php
namespace SalmonDE\StatsPE\Commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use SalmonDE\StatsPE\Base;

class StatsCmdExecutor implements \pocketmine\command\CommandExecutor
{

    public function onCommand(\pocketmine\command\CommandSender $sender, \pocketmine\command\Command $cmd, $label, array $args){
        if(!isset($args[0])){
            if(!$sender instanceof Player){
                return false;
            }
            $args[0] = $sender->getName();
        }

        if(is_array($data = Base::getInstance()->getDataProvider()->getAllData($args[0]))){
            $text = str_replace('{value}', $data['Username'], Base::getInstance()->getMessage('general.header'));
            foreach(Base::getInstance()->getDataProvider()->getEntries() as $entry){
                if($sender->hasPermission('statspe.entry.'.$entry->getName())){
                    switch($entry->getName()){
                        case 'FirstJoin':
                            $p = $sender->getServer()->getOfflinePlayer($args[0]);
                            $value = date(Base::getInstance()->getConfig()->get('Date-Format'), $p->getFirstPlayed() / 1000);
                            break;

                        case 'LastJoin':
                            $p = $sender->getServer()->getOfflinePlayer($args[0]);
                            $value = date(Base::getInstance()->getConfig()->get('Date-Format'), $p->getLastPlayed() / 1000);
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

                        case 'Online':
                            $value = $data['Online'] ? Base::getInstance()->getMessage('commands.stats.true') : Base::getInstance()->getMessage('commands.stats.false');
                            break;

                        default:
                            $value = $data[$entry->getName()];
                    }
                    $text .= TF::RESET."\n".TF::AQUA.$entry->getName().': '.TF::GOLD.$value;
                }
            }
            $sender->sendMessage($text);
        }else{
            $sender->sendMessage(TF::RED.str_replace('{player}', $args[0], Base::getInstance()->getMessage('commands.stats.notFound')));
        }
        return true;
    }
}
