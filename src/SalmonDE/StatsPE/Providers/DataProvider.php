<?php
namespace SalmonDE\StatsPE\Providers;

interface DataProvider
{

    public function initialize(array $data);

    public function addPlayer(\pocketmine\Player $player);

    public function getData(string $player, Entry $entry);

    public function getAllData() : array;

    public function saveData(string $player, Entry $entry, $value);

    public function addEntry(Entry $entry);

    public function removeEntry(Entry $entry);

    public function getEntries() : array;

    public function entryExists(Entry $entry) : bool;

    public function saveAll();
}
