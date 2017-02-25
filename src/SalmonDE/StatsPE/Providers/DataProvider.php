<?php
namespace SalmonDE\StatsPE\Providers;

interface DataProvider
{

    const INT = 0;
    const FLOAT = 1;
    const STRING = 2;

    public function initialize(array $data);

    public function getData(string $player, string $entry);

    public function getCompleteData() : array;

    public function saveData(string $player, string $entry, $value);

    public function addEntry(string $entry, int $expectedType);

    public function removeEntry(string $entry);

    public function validEntry(string $entry) : bool;
}
