<?php
namespace SalmonDE\StatsPE\Providers;

class Entry {

    const TYPE_INT = 0;
    const TYPE_FLOAT = 1;
    const TYPE_STRING = 2;
    const TYPE_ARRAY = 3;
    const TYPE_BOOL = 4;

    private $name;
    private $defaultValue;
    private $expectedType;
    private $storesData;
    private $unsigned;

    public function __construct(string $name, $defaultValue, int $expectedType, bool $storesData, bool $unsigned = false){
        $this->name = $name;
        $this->expectedType = $expectedType;

        if($this->isValidType($default)){
            $this->defaultValue = $defaultValue;
            $this->storesData = $storesData;
        }
    }

    public function getName(): string{
        return $this->name;
    }

    public function getExpectedType(): int{
        return $this->expectedType;
    }

    public function getDefaultValue(): mixed{
        return $this->defaultValue;
    }

    public final function isValidType($value): bool{
        switch($this->expectedType){
            case self::TYPE_INT:
                return is_int($value);

            case self::TYPE_FLOAT:
                return is_float($value);

            case self::TYPE_STRING:
                return is_string($value);

            case self::TYPE_ARRAY:
                return is_array($value);

            case self::TYPE_BOOL:
                return is_bool($value);
        }

        return false;
    }

    public function toDbValue($value): mixed{
        switch($this->expectedType){
            case self::TYPE_INT:
            case self::TYPE_FLOAT:
            case self::TYPE_STRING:
                return $value;

            case self::TYPE_ARRAY:
                return json_encode($value);

            case self::TYPE_BOOL:
                return (int) (bool) $value;
        }
    }

    public function fromDbValue($value): mixed{
        switch($this->expectedType){
            case self::TYPE_INT:
                return (int) $value;

            case self::TYPE_FLOAT:
                return (float) $value;

            case self::TYPE_STRING:
                return (string) $value;

            case self::TYPE_ARRAY:
                return (array) json_decode($value, true);

            case self::TYPE_BOOL:
                return (bool) $value;
        }
    }

    public function isUnsigned(): bool{
        return ($this->unsigned && $this->expectedType === self::INT);
    }

    public function storesData(): bool{
        return $this->storesData;
    }

}
