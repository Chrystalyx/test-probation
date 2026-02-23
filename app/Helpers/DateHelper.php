<?php

namespace App\Helpers;

use Carbon\Carbon;
use DateTime;

class DateHelper
{
    public static function getCurrentDate($format = "Y-m-d H:i:s", $time_zone = null)
    {
        if ($time_zone) {
            return Carbon::now($time_zone)->format($format);
        } else {
            return Carbon::now()->format($format);
        }
    }

    public static function addDateTime($unit, $src, $n, $format = "")
    {
        if ($src == 'now') {
            $s = Carbon::now();
        } else {
            $s = Carbon::parse($src);
        }
        $unit = "add" . ucwords(strtolower($unit)) . "s";
        $ret = $s->$unit($n);
        if (!empty($format)) {
            return $ret->format($format);
        } else {
            return $ret;
        }
    }

    public static function compareDate($src, $dest, $comp = 'eq')
    {
        $s = ($src == 'now') ? Carbon::now() : Carbon::parse($src);
        $d = ($dest == 'now') ? Carbon::now() : Carbon::parse($dest);
        return $s->$comp($d);
    }

    public static function getDateTimeDiff($unit, $src, $dest)
    {
        $s = Carbon::parse($src);
        $d = Carbon::parse($dest);
        $unit = "diffIn" . ucwords(strtolower($unit)) . "s";
        return $s->$unit($d, false);
    }

    public static function currentDateTime($user_timezone = 'Asia/Jakarta')
    {
        $date = new \DateTime("now", new \DateTimeZone($user_timezone));

        $only_date = $date->format('Y-m-d');
        $only_time = $date->format('H:i:s');
        $datetime = $date->format('Y-m-d H:i:s');

        return [
            'date' => $only_date,
            'time' => $only_time,
            'datetime' => $datetime
        ];
    }

    public static function parsingDate($src, $format = "Y-m-d H:i:s", $opts = [])
    {
        $res = Carbon::parse($src)->format($format);
        return $res;
    }

    public static function dateFormat($src, $format = 'Y-m-d H:i:s')
    {
        return Carbon::parse($src)->format($format);
    }

    public static function calculateTotalDays($start, $end)
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        return $startDate->diffInDays($endDate) + 1;
    }

    public static function isDateInRange($selectedDate, $startDate, $endDate)
    {
        $selectedDateTime = new DateTime($selectedDate);
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);

        return ($selectedDateTime >= $startDateTime && $selectedDateTime <= $endDateTime);
    }
}
