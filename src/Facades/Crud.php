<?php

namespace Khaled\CrudSystem\Facades;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

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

    public static function formatCommandInfo(Command $command, string $message): void
    {
        $infoColorCode = "\033[44m";
        $endCode = "\033[0m";
        $command->newLine();
        $command->line("  {$infoColorCode} INFO {$endCode} {$message}");
        $command->newLine();
    }

    public static function formatCommandRunGenerator($text, $type, $time = null)
    {
        $statusColorCode = $type === 'done' ? "\033[32m" : "\033[33m";
        $endCode = "\033[0m";
        $dotsCode = "\033[2;37m";
        $status = strtoupper($type);
        $second = $time && $time >= 1000 ? 's' : 'ms';
        $time = $time ? ($time >= 1000 ? $time / 1000 : $time) : null;
        $time = $time ? " {$time} {$second}" : "";
        
        // Get the terminal width using tput
        $terminalWidth = (int)exec('tput cols');
        if ($terminalWidth === 0) {
            $terminalWidth = 80; // Fallback width if tput fails
        }
        $terminalWidth -= 4; // Adjust for padding
        
        // Calculate remaining space after the text and spaces around the status and time
        $dotsCount = max(0, $terminalWidth - strlen($text) - strlen($status) - strlen($time) - 4); // 4 for spaces around status and time
        $dots = str_repeat('.', $dotsCount);
        return "  {$text} {$dotsCode}{$dots}{$time}{$endCode} {$statusColorCode}{$status}{$endCode}  ";
    }
    
}
