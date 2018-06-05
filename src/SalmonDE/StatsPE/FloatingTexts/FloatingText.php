<?php
namespace SalmonDE\StatsPE\FloatingTexts;

use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use SalmonDE\StatsPE\StatsBase;
use SalmonDE\StatsPE\Utils;

class FloatingText extends FloatingTextParticle {

    private $owner;
    private $name;
    private $floatingText = [];
    private $levelName;

    public function __construct(StatsBase $owner, string $name, int $x, int $y, int $z, string $levelName, array $text){
        parent::__construct(new Vector3($x, $y, $z), '');
        $this->owner = $owner;
        $this->name = $name;
        $this->floatingText = $text;
        $this->levelName = $levelName;
    }

    public function getName(): string{
        return $this->name;
    }

    public function getFloatingText(): array{
        return $this->floatingText;
    }

    public function getLevelName(): string{
        return $this->levelName;
    }

    public function sendTextToPlayer(Player $player){
        $data = $this->owner->getDataProvider()->getAllData($player->getName());
        $text = [];
        foreach(array_keys($this->floatingText) as $key){
            if(StatsBase::getEntryManager()->entryExists($key)){
                switch($key){
                    case 'FirstJoin':
                        $value = date($this->owner->getConfig()->get('dateFormat'), $player->getFirstPlayed() / 1000);
                        break;

                    case 'LastJoin':
                        $value = date($this->owner->getConfig()->get('dateFormat'), $player->getLastPlayed() / 1000);
                        break;

                    case 'OnlineTime':
                        $seconds = $data['OnlineTime'];
                        $seconds += ceil(microtime(true) - ($player->getLastPlayed() / 1000));

                        $value = Utils::getPeriodFromSeconds($seconds);
                        break;

                    case 'K/D':
                        $value = Utils::getKD($data['KillCount'], $data['DeathCount']);
                        break;

                    case 'Online':
                        $value = $data['Online'] ? $this->owner->getMessage('commands.stats.true') : $this->owner->getMessage('commands.stats.false');
                        break;

                    default:
                        $value = $data[$key];
                }
                $text[] = str_replace('{value}', $value, $this->floatingText[$key]);
            }
        }

        $this->setTitle(array_shift($text));
        $text = implode(TF::RESET."\n", $text);

        $this->setText($text);
        $player->getLevel()->addParticle($this, [$player]);
        $this->setTitle(' ');
        $this->setText(' ');
    }

    public function removeTextForPlayer(Player $player){
        $this->setInvisible();
        $player->getLevel()->addParticle($this, [$player]);
        $this->setInvisible(false);
    }

}
