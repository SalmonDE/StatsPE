<?php
namespace SalmonDE\StatsPE\Providers;

interface DataProvider
{

    public function initialize(array $data);

    public function getName() : string;

    public function getData(string $player, Entry $entry);

    public function getAllData(string $player = null);

    public function saveData(string $player, Entry $entry, $value);

    public function addEntry(Entry $entry);

    public function removeEntry(Entry $entry);

    public function getEntries() : array;

    public function entryExists(string $entry) : bool;

    public function countDataRecords() : int;

    public function saveAll();
}
