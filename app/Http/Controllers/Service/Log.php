<?php

namespace App\Http\Controllers\Service;

class Log
{
    public static function write (string $content, bool $append = true)
    {
        if ($append) {
            return file_put_contents(env("LOG_FILE"), self::format($content), FILE_APPEND);
        }

        return file_put_contents(env("LOG_FILE"), self::format($content));
    }

    public static function format (string $content)
    {
        return "[" . date("Y-m-d H:i:s") . "] " . $content . "\n";
    }
}