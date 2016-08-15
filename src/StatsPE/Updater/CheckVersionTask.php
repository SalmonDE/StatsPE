<?php

namespace StatsPE\Updater;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Utils;

class CheckVersionTask extends AsyncTask
{
    public function __construct($owner)
    {
        $this->name = $owner->getDescription()->getName();
        $this->cversion = $owner->getDescription()->getVersion();
        $this->website = $owner->getDescription()->getWebsite();
        $this->autoupdate = $owner->getConfig()->get('Auto-Update');
        $this->path = $owner->getDataFolder();
    }

    public function onRun()
    {
        $nversion = str_replace(array(' ', "\r", "\n"), '', Utils::getURL($this->website.'MCPE-Plugins/'.$this->name.'/Updater.php?check'));
        $cversion = $this->cversion;
        if (!$nversion) {
            file_put_contents($this->path.'version', 'NULL');
        } else {
            file_put_contents($this->path.'version', $nversion);
        }
    }
}
