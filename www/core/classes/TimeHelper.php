<?php

class TimeHelper
{
    public static function intToCurrencyStr($val) {
        $res = '$'.round($val/100, 2);
        return $res;
    }
    
    public static function timeToStr($val) {
        $res = date('Y-m-d H:i:s', $val);
        return $res;
    }
    
    public static function periodToStr($val, $shortFormat = true, $locale = 'rus') {
        $result = '';
        switch ($locale) {
            case 'rus':
                    $result = self::periodToStrRus($val, $shortFormat);
                break;
            default:
                break;
        }
        return $result;
    }
    
    public static function periodToStrRus($val, $shortFormat = true) {
        $s = '';
        $minute = 60;
        $hour = 3600;
        $day = 86400;
        
        $days = floor($val/$day);
        $hours = floor(($val % $day)/$hour);
        $minutes = floor(($val % $hour)/$minute);
        $seconds = ($val % $minute);
        
        if ($shortFormat) {
            if ($days > 0) $s .= $days.'д ';
            if ($hours != 0) $s .= self::leadZero($hours);
            if (($minutes != 0) || ($seconds != 0)) $s .= ':'.self::leadZero($minutes);
            else if ($hours != 0) $s .= 'ч';
            if ($seconds != 0) $s .= ':'.self::leadZero($seconds);
            //else if ($minutes != 0) $s .= 'м';
        }
        else {
            if ($days > 0) $s .= $days.' '.self::numPadej($days, 'день', 'дня', 'дней').' ';
            if ($hours > 0) $s .= $hours.' '.self::numPadej($hours, 'час', 'часа', 'часов').' ';
            if ($minutes > 0) $s .= $minutes.' '.self::numPadej($minutes, 'минута', 'минуты', 'минут').' ';
            if ($seconds > 0) $s .= $seconds.' '.self::numPadej($seconds, 'секунда', 'секунды', 'секунд');
        }
        return $s;
    }
    
    private static function leadZero($val) {
        if ($val < 10) $val = '0'.$val;
        return $val;
    }
    
    private static function numPadej($val, $s1, $s2, $s3) {
        $s = '';
        if ($val < 2) $s = $s1;
        elseif ($val < 5) $s = $s2;
        else $s = $s3;
        return $s;
    }
}
