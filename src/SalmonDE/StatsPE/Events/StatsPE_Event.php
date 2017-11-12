<?php
namespace SalmonDE\StatsPE\Events;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

class StatsPE_Event extends PluginEvent
{

    public static $handlerList = null;

    public function __construct(Plugin $plugin){
        parent::__construct($plugin);
    }
}
