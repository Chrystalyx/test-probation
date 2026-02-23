<?php

namespace App\Helpers;

use App\Models\Purchases;
use App\Models\Sales;
use Carbon\Carbon;

class AutoNumberHelper
{
    public static function initGenerateNumber($prefix, $date = '')
    {
        $data = [];

        if ($prefix == null || $prefix == '') {
            return response()->json(['status' => 'error', 'data' => '', 'message' => 'Prefix should exist!']);
        } else {
            switch ($prefix) {
                case "PO":
                    $data = ['class' => Purchases::class, 'field' => 'number', 'prefix' => $prefix];
                    break;
                case "SO":
                    $data = ['class' => Sales::class, 'field' => 'number', 'prefix' => $prefix];
                    break;
                default:
                    return response()->json(['status' => 'error', 'message' => 'Invalid prefix!']);
            }
        }

        return self::generateNumber($data, $date);
    }

    private static function generateNumber($params, $date)
    {
        $now = Carbon::now();
        $prefixSize = (strlen($params['prefix'])) + 10;

        $month_param = $now->month;
        $year_param = $now->year;

        if ($date != '') {
            $expl_date = explode('-', $date);
            if (count($expl_date) > 1) {
                $month_param = $expl_date[1];
                $year_param = $expl_date[0];
            }
        }

        $prefix = $params['prefix'];
        $prefix .= $year_param . sprintf('%02d', $month_param);

        $data = $params['class']::whereRaw('LENGTH(' . $params['field'] . ') = ?', $prefixSize)
            ->where($params['field'], 'LIKE', $prefix . '%')->orderBy('id', 'DESC')
            ->first();

        if ($data == null) {
            $prefix .= sprintf('%04d', 1);
        } else {
            $repeat = true;
            $last = substr($data[$params['field']], -4);
            $last = ++$last;

            $new = sprintf('%04d', $last);
            while ($repeat)
            {
                $data = $params['class']::where($params['field'], $prefix . $new)->first();

                if ($data == null) {
                    $repeat = false;
                    $prefix .= sprintf('%04d', $new);
                } else {
                    $new = sprintf('%04d', ++$new);
                }
            }
        }
        return $prefix;
    }

    public static function generateCode($params)
    {
        $code = '';
        $field = $params['field'];
        $prefix = $params['prefix'];
        $width = 4;
        $padding = '0';
        $separator = '-';

        $data = $params['class']::select($field)
            ->where($field, 'LIKE', $prefix . $separator . '%')
            ->orderBy($field, 'DESC')
            ->first();

        $format = '%'.$padding.$width.'d';
        if ($data == null) {
            $code = $prefix . $separator . sprintf($format, 1);
        } else {
            $last = substr($data[$field], -$width);
            $code = $prefix . $separator . sprintf($format, $last+1);
        }
        return $code;
    }
}
