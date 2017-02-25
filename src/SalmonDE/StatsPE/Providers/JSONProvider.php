<?php
namespace SalmonDE\StatsPE\Providers;

class JSONProvider implements DataProvider
{

    public function __construct(string $path){
        $this->initialize(['path' => $path]);
    }

    public function initialize(array $data){

    }

    public function getData(string $player, string $entry){

    }

    public function getCompleteData() : array{

    }

    public function saveData(string $player, string $entry, $value){

    }

    public function addEntry(string $entry, int $expectedType){

    }

    public function removeEntry(string $entry){

    }

    public function validEntry(string $entry) : bool{

    }
}
