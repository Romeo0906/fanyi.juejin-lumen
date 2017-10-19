<?php

namespace App\Http\Controllers\Service;

class Log
{
    public static function write (string $content, bool $append = true)
    {
        if ($append) {
            return file_put_contents(self::format($content), env("LOG_FILE"), FILE_APPEND);
        }

        return file_put_contents(self::format($content), env("LOG_FILE"));
    }

    public static function format (string $content)
    {
        return "[" . date("Y-m-d H:i:s") . "] " . $content . "\n";
    }
}