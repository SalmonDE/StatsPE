<?php
namespace SalmonDE\StatsPE\FloatingTexts;

use pocketmine\level\Level;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;
use SalmonDE\StatsPE\StatsBase;
use SalmonDE\StatsPE\FloatingTexts\Events\FloatingTextEvent;

class FloatingTextManager {

    private $owner;
    private $floatingTextConfig;
    private $floatingTexts = [];

    public function __construct(StatsBase $owner){
        $this->owner = $owner;
        $this->floatingTextConfig = new Config($owner->getDataFolder().'floatingtexts.yml', Config::YAML);

        foreach($this->floatingTextConfig->getAll(true) as $key){
            $data = $this->floatingTextConfig->get($key);
            $this->floatingTexts[$data['Position']['Level']][$key] = new FloatingText($key, $data['Position']['X'], $data['Position']['Y'], $data['Position']['Z'], $data['Position']['Level'], $data['Text']);
        }
        $owner->getServer()->getPluginManager()->registerEvents(new EventListener(), $owner);
    }

    public function getFloatingText(string $name){
        if(($data = $this->floatingTextConfig->get($name)) !== false){
            return $this->floatingTexts[$data['Position']['Level']][$name];
        }
    }

    public function getAllFloatingTexts(): array{
        return $this->floatingTexts;
    }

    public function addFloatingText(string $name, int $x, int $y, int $z, Level $level){
        if($this->getFloatingText($name) instanceof FloatingText){
            return false;
        }

        $x = round($x);
        $y = round($y);
        $z = round($z);

        foreach($this->owner->getDataProvider()->getEntries() as $entry){
            $text[$entry->getName()] = TF::AQUA.$entry->getName().': '.TF::GOLD.'{value}';
        }
        $text['Username'] = $this->owner->getMessage('general.header');

        $event = new FloatingTextEvent($this->owner, new FloatingText($name, $x, $y, $z, $level->getFolderName(), $text), FloatingTextEvent::ADD);
        $this->owner->getServer()->getPluginManager()->callEvent($event);

        if(!$event->isCancelled()){
            $this->floatingTexts[$level->getFolderName()][$name] = $event->getFloatingText();
            foreach($level->getPlayers() as $player){
                $this->floatingTexts[$level->getFolderName()][$name]->sendTextToPlayer($player);
            }

            $data = [
                'Position' => [
                    'X' => $x,
                    'Y' => $y,
                    'Z' => $z,
                    'Level' => $level->getFolderName()
                ],
                'Text' => $text
            ];

            $this->floatingTextConfig->__set($name, $data);
            $this->floatingTextConfig->save(true);
            return true;
        }
        return false;
    }

    public function removeFloatingText(string $name){
        if(!($floatingText = $this->getFloatingText($name)) instanceof FloatingText){
            return false;
        }

        $event = new FloatingTextEvent($this->owner, $floatingText, FloatingTextEvent::REMOVE);
        $this->owner->getServer()->getPluginManager()->callEvent($event);

        if(!$event->isCancelled()){
            foreach($this->owner->getServer()->getLevelByName($floatingText->getLevelName())->getPlayers() as $player){
                $this->floatingTexts[$player->getLevel()->getFolderName()][$name]->removeTextForPlayer($player);
            }
            unset($this->floatingTexts[$player->getLevel()->getFolderName()][$name]);

            $this->floatingTextConfig->__unset($name);
            $this->floatingTextConfig->save(true);
            return true;
        }
        return false;
    }

}
