<?php

namespace App\Helpers;

class Env
{
    private static array $vars = [];

    public static function load(string $path): void
    {
        if (!file_exists($path)) return;

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key   = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            self::$vars[$key] = $value;
            putenv("$key=$value");
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        return self::$vars[$key] ?? getenv($key) ?: $default;
    }
}
