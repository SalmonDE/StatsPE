<?php
namespace SalmonDE\StatsPE;

class Utils
{

    public static function getPeriodFromSeconds(int $seconds) : string{
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

    public static function getKD(int $kills, int $deaths) : float{
        return round($kills / $deaths !== 0 ? $deaths : 1), 2);
    }
}
