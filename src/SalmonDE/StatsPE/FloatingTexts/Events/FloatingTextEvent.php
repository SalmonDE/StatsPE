<?php
namespace SalmonDE\StatsPE\FloatingTexts\Events;

use pocketmine\event\Cancellable;
use pocketmine\plugin\Plugin;
use SalmonDE\StatsPE\Events\StatsPE_Event;
use SalmonDE\StatsPE\FloatingTexts\FloatingText;

class FloatingTextEvent extends StatsPE_Event implements Cancellable {

    public static $handlerList = null;

    const ADD = 0;
    const REMOVE = 1;

    private $floatingText;
    private $type;

    public function __construct(Plugin $plugin, FloatingText $floatingText, int $type){
        parent::__construct($plugin);
        $this->floatingText = $floatingText;
        $this->type = $type;
    }

    public function getFloatingText(): FloatingText{
        return $this->floatingText;
    }

    public function getType(){
        return $this->type;
    }

}
