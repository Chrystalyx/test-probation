<?php

namespace App\Helpers;

class GlobalHelper
{
    public static function findString($needle, $haystack, $i, $word)
    {
        if (strtoupper($word) == "W") {
            if (preg_match("/\b{$needle}\b/{$i}", $haystack)) {
                return true;
            }
        } else {
            if (preg_match("/{$needle}/{$i}", $haystack)) {
                return true;
            }
        }
        return false;
    }

    public static function convertSeparator($number, $separator = ',')
    {
        $number = str_replace($separator, '', $number);

        if ($number > 0) {
            return floatval($number);
        }

        return 0;
    }

    public static function camelToSnake($camel)
    {
        $snake = preg_replace('/[A-Z]/', '_$0', $camel);
        $snake = strtolower($snake);
        $snake = ltrim($snake, '_');
        return $snake;
    }

    public static function getClientIP()
    {
        $ip = 'Unknown';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $ip_address = explode(',', $ip);
        return $ip_address[0];
    }

    public static function escapeJsonString($value) 
    {  
        $escapers = ["\n"];
        $replacements = [", "];
        $result = str_replace($escapers, $replacements, $value);
        return $result;
    }

    public static function randomText($length = 8, $type = 'alnum')
    {
        switch ($type) {
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            default:
                $pool = (string) $type;
                break;
        }

        $crypto_rand_secure = function ($min, $max) {
            $range = $max - $min;
            if ($range < 0) return $min;
            $log    = log($range, 2);
            $bytes  = (int) ($log / 8) + 1;
            $bits   = (int) $log + 1;
            $filter = (int) (1 << $bits) - 1;
            do {
                $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
                $rnd = $rnd & $filter;
            } while ($rnd >= $range);
            return $min + $rnd;
        };

        $token = "";
        $max   = strlen($pool);
        for ($i = 0; $i < $length; $i++) {
            $token .= $pool[$crypto_rand_secure(0, $max)];
        }

        return $token;
    }

    public static function slugify($text, string $divider = '_')
    {
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, $divider);
        $text = preg_replace('~-+~', $divider, $text);
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public static function findArrayByValue($params, $key, $value)
    {
        $res = false;

        if ($params) {
            foreach ($params as $param) {
                if ($param[$key] == $value) {
                    $res = true;
                    continue;
                }
            }
        }

        return $res;
    }

    public static function formatNumber($value)
    {
        return number_format($value, 0, ',', '.');
    }

    public static function showFailedAlert($msg, $selectedIcon = 'error', $selectedTitle = 'Failed')
    {
        return [
            'title' => $selectedTitle,
            'message' => $msg,
            'icon' => $selectedIcon
        ];
    }
}
