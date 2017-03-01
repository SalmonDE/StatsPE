<?php
namespace SalmonDE\Providers;

class Entry
{
    const INT = 0;
    const FLOAT = 1;
    const STRING = 2;
    const ARRAY = 3;
    const BOOL = 4;
    const MIXED = 5;

    private $name;
    private $defaultValue;
    private $expectedType;
    private $valid = false;
    private $shouldSave = false;

    public function __construct(string $name, $default, int $type = self::MIXED, $shouldSave = true){
        $this->name = $name;
        $this->expectedType = $type;
        if($this->isValidType($default)){
            $this->defaultValue = $default;
            $this->shouldSave = $shouldSave;
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

            case self::ARRAY:
                if(is_array($value)){
                    return true;
                }
                break;

            case self::BOOL:
                if(is_bool($value)){
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

    public function shouldSave() : bool{
        return $this->shouldSave;
    }
}
