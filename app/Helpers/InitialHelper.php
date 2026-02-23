<?php

namespace App\Helpers;

class InitialHelper
{
    /**
     * Generate initials from a name
     */
    public static function generate(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        $name = preg_replace('/[^a-zA-Z\s\p{L}]/u', '', $name);

        $parts = explode(' ', $name);
        $initials = '';
        foreach ($parts as $part) {
            if ($part !== '') {
                $initials .= mb_strtoupper(mb_substr($part, 0, 1));
            }
        }

        return $initials;
    }

    public static function spellNumberInIndonesian($number)
    {
        $result = "";
        $number = strval($number);
        if (!preg_match("/^[0-9]{1,15}$/", $number)) return false;

        $ones       = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan"];
        $majorUnits = ["", "ribu", "juta", "milyar", "trilyun"];
        $minorUnits = ["", "puluh", "ratus"];
        $length     = strlen($number);
        $isAnyMajorUnit = false;

        for ($i = 0, $pos = $length - 1; $i < $length; $i++, $pos--) {
            if ($number[$i] != '0') {
                if ($number[$i] != '1') {
                    $result .= $ones[$number[$i]] . ' ' . $minorUnits[$pos % 3] . ' ';
                } else if ($pos % 3 == 1 && $number[$i + 1] != '0') {
                    if ($number[$i + 1] == '1')
                        $result .= "sebelas ";
                    else
                        $result .= $ones[$number[$i + 1]] . " belas ";
                    $i++;
                    $pos--;
                } else if ($pos % 3 != 0) {
                    $result .= "se" . $minorUnits[$pos % 3] . ' ';
                } else if ($pos == 3 && !$isAnyMajorUnit) {
                    $result .= "se";
                } else {
                    $result .= "satu ";
                }
                $isAnyMajorUnit = true;
            }

            if ($pos % 3 == 0 && $isAnyMajorUnit) {
                $result         .= $majorUnits[$pos / 3] . ' ';
                $isAnyMajorUnit = false;
            }
        }
        $result = trim($result);
        if ($result == "") $result = "nol";

        return ucwords($result);
    }

    public static function convertSeparator($number)
    {
        $number = str_replace('.', '', $number);

        if ($number > 0) {
            return $number;
        }

        return 0;
    }
}
