<?php
namespace SalmonDE\StatsPE\Providers;

interface DataProvider
{

    const INT = 0;
    const FLOAT = 1;
    const STRING = 2;
    const BOOL = 3;

    public function initialize(array $data);

    public function addPlayer(\pocketmine\Player $player);

    public function getData(string $player, string $entry);

    public function getAllData() : array;

    public function saveData(string $player, string $entry, $value);

    public function addEntry(string $entry, int $expectedType, $default);

    public function removeEntry(string $entry);

    public function getEntries() : array;

    public function validEntry(string $entry) : bool;

    public function isStrict() : bool;

    public function saveAll();
}
