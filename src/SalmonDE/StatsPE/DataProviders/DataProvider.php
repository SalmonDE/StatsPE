<?php
namespace SalmonDE\StatsPE\DataProviders;

abstract class DataProvider {

    public function initialize(array $data);

    public final function getName(): string{
        return get_class();
    }

    public function getData(string $player, Entry $entry);

    public function getDataWhere(Entry $needleEntry, $needle, array $wantedEntries);

    public function getAllData(string $player = null);

    public function saveData(string $player, Entry $entry, $value);

    public function incrementValue(string $player, Entry $entry, int $int = 1);

    public function countDataRecords(): int;

    public function saveAll();
}
