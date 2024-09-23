<?php

namespace W88\CrudSystem\Facades;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Crud
{
    private static function getConfig($file)
    {
        $path = __DIR__ . "/../config/{$file}.php";
        if (!File::exists($path)) return [];
        return File::getRequire($path) ?? [];
    }

    public static function config($key, $default = null)
    {
        $fileName = Str::before($key, '.');
        $key = Str::after($key, '.');
        return Arr::get(self::getConfig($fileName), $key, $default);
    }
}
