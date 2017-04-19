<?php
namespace SalmonDE\StatsPE\Commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use SalmonDE\StatsPE\Base;
use SalmonDE\StatsPE\FloatingTexts\FloatingTextManager;

class StatsPECmdExecutor implements \pocketmine\command\CommandExecutor
{

    public function onCommand(\pocketmine\command\CommandSender $sender, \pocketmine\command\Command $cmd, $label, array $args){
        if(isset($args[0])){
            if(strtolower($args[0]) === 'floatingtext' && isset($args[1])){
                if(!$sender->hasPermission('statspe.cmd.statspe.floatingtext')){
                    $sender->sendMessage(new \pocketmine\event\TranslationContainer(TF::RED.'%commands.generic.permission'));
                    return true;
                }

                switch(strtolower($args[1])){
                    case 'add':
                        if(!$sender instanceof Player){
                            Base::getInstance()->getMessage('commands.statspe.floatingtext.senderNotPlayer');
                        }
                        if(isset($args[2])){
                            if(FloatingTextManager::getInstance()->addFloatingText($args[2], $sender->x, $sender->y, $sender->z, $sender->getLevel())){
                                $sender->sendMessage(str_replace('{name}', $args[2], Base::getInstance()->getMessage('commands.statspe.floatingtext.addSuccess')));
                            }else{
                                $sender->sendMessage(str_replace('{name}', $args[2], Base::getInstance()->getMessage('commands.statspe.floatingtext.alreadyExists')));
                            }
                        }else{
                            $sender->sendMessage(Base::getInstance()->getMessage('commands.statspe.floatingtext.missingName'));
                        }
                        break;

                    case 'remove':
                        if(isset($args[2])){
                            if(FloatingTextManager::getInstance()->removeFloatingText($args[2])){
                                $sender->sendMessage(str_replace('{name}', $args[2], Base::getInstance()->getMessage('commands.statspe.floatingtext.removeSuccess')));
                            }else{
                                $sender->sendMessage(Base::getInstance()->getMessage(str_replace('{name}', $args[2], 'commands.statspe.floatingtext.notFound')));
                            }
                        }else{
                            $sender->sendMessage(Base::getInstance()->getMessage('commands.statspe.floatingtext.missingName'));
                        }
                        break;

                    case 'list':
                        foreach(FloatingTextManager::getInstance()->getAllFloatingTexts() as $levelList){
                            foreach($levelList as $floatingText){
                                $texts[] = $floatingText->getName();
                            }
                        }

                        $sender->sendMessage(str_replace(['{count}', '{names}'], [count($texts), implode(', ', $texts)], Base::getInstance()->getMessage('commands.statspe.floatingtext.listAll')));
                        break;

                    case 'info':
                        if(isset($args[2])){
                            if(($ft = FloatingTextManager::getInstance()->getFloatingText($args[2])) instanceof \SalmonDE\StatsPE\FloatingTexts\FloatingText){
                                $info = [
                                    '{name}' => $ft->getName(),
                                    '{x}' => $ft->x,
                                    '{y}' => $ft->y,
                                    '{z}' => $ft->z,
                                    '{level}' => $ft->getLevelName(),
                                    '{entries}' => implode(', ', array_keys($ft->getFloatingText()))
                                ];

                                $lines = [
                                    Base::getInstance()->getMessage('commands.statspe.floatingtext.info.name'),
                                    Base::getInstance()->getMessage('commands.statspe.floatingtext.info.position'),
                                    Base::getInstance()->getMessage('commands.statspe.floatingtext.info.entries')
                                ];
                                $lines = implode(TF::RESET."\n", $lines);

                                $sender->sendMessage(str_replace(array_keys($info), array_values($info), $lines));
                            }else{
                                $sender->sendMessage(Base::getInstance()->getMessage(str_replace('{name}', $args[2], 'commands.statspe.floatingtext.notFound')));
                            }
                        }else{
                            $sender->sendMessage(Base::getInstance()->getMessage('commands.statspe.floatingtext.missingName'));
                        }
                        break;
                    default:
                        return false;
                }
            }else{
                return false;
            }
        }else{
            $messages = [
                Base::getInstance()->getMessage('commands.statspe.header'),
                Base::getInstance()->getMessage('commands.statspe.author'),
                Base::getInstance()->getMessage('commands.statspe.api'),
                Base::getInstance()->getMessage('commands.statspe.provider'),
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
                '{provider}' => Base::getInstance()->getDataProvider()->getName(),
                '{records_amount}' => Base::getInstance()->getDataProvider()->countDataRecords(),
                '{entries}' => implode('; ', $entries)
            ];

            $sender->sendMessage(str_replace(array_keys($values), array_values($values), implode(TF::RESET."\n", $messages)));
        }
        return true;
    }
}
