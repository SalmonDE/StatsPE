<?php
namespace SalmonDE\StatsPE\Providers;

interface DataProvider
{

    public function initialize(array $data);

    public function addPlayer(\pocketmine\Player $player);

    public function getData(string $player, string $entry);

    public function getAllData() : array;

    public function saveData(string $player, string $entry, $value);

    public function addEntry(string $entry, int $expectedType, $default);

    public function removeEntry(string $entry);

    public function getEntries() : array;

    public function validEntry(string $entry) : bool;

    public function saveAll();
}
