<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class StringHelper
{
    public static function kebab(string $string): string
    {
        $map = [
            'š' => 's',
            'đ' => 'd',
            'č' => 'c',
            'ć' => 'c',
            'ž' => 'z',
            'Š' => 's',
            'Đ' => 'd',
            'Č' => 'c',
            'Ć' => 'c',
            'Ž' => 'z',
        ];
        $string = str_replace(array_keys($map), array_values($map), $string);

        return Str::kebab($string);
    }
}
