<?php
namespace SalmonDE\Providers;

class Entry
{
    const INT = 0;
    const FLOAT = 1;
    const STRING = 2;
    const MIXED = 3;

    private $name;
    private $defaultValue;
    private $expectedType;
    private $valid = false;

    public function __construct(string $name, $default, int $type = self::MIXED){
        $this->name = $name;
        $this->expectedType = $type;
        if($this->isValid($default)){
            $this->defaultValue = $default;
            $this->valid = true;
        }
    }

    public function getName() : string{
        return $this->name;
    }

    public function getExpectedType() : int{
        return $this->expectedType;
    }

    public function getDefault(){
        return $this->defaultValue;
    }

    public function isValidType($value) : bool{
        switch($this->expectedType){
            case self::INT:
                if(is_int($value)){
                    return true;
                }
                break;
            case self::FLOAT:
                if(is_float($value)){
                    return true;
                }
                break;
            case self::STRING:
                if(is_string($value)){
                    return true;
                }
                break;
            case self::MIXED:
                return true;
        }
        return false;
    }

    public function isValid() : bool{
        return $this->valid;
    }
}
