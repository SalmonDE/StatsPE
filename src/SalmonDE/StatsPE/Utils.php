<?php
declare(strict_types = 1);

namespace SalmonDE\StatsPE;

use SalmonDE\StatsPE\Entries\Entry;

class Utils {

    public static function getPeriodFromSeconds(int $seconds): string{
        $ref = new \DateTime(date('Y-m-d H:i:s', 0));
        $time = $ref->diff(new \DateTime(date('Y-m-d H:i:s', $seconds)));

        $time = ($time->y !== 0 ? $time->y.'y ' : '').($time->m !== 0 ? $time->m.'m ' : '').($time->d !== 0 ? $time->d.'d ' : '').$time->h.'h '.$time->i.'i '.$time->s.'s';

        $units = [
            Base::getInstance()->getMessage('general.onlinetime.years'),
            Base::getInstance()->getMessage('general.onlinetime.months'),
            Base::getInstance()->getMessage('general.onlinetime.days'),
            Base::getInstance()->getMessage('general.onlinetime.hours'),
            Base::getInstance()->getMessage('general.onlinetime.minutes'),
            Base::getInstance()->getMessage('general.onlinetime.seconds')
        ];
        return str_replace(['y', 'm', 'd', 'h', 'i', 's'], $units, $time);
    }

    public static function getKD(int $kills, int $deaths): float{
        return round($kills / ($deaths !== 0 ? $deaths : 1), 2);
    }

    public static function getMySQLDatatype(int $type): string{
        switch($type){
            case Entry::TYPE_INT:
                return 'BIGINT(255)';

            case Entry::TYPE_FLOAT:
                return 'DECIMAL(65, 3)';

            case Entry::TYPE_STRING:
                return 'VARCHAR(255)';

            case Entry::TYPE_ARRAY:
                return 'VARCHAR(255)';

            case Entry::TYPE_BOOL:
                return 'BIT(1)';
        }
    }
}
