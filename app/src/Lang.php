<?php
namespace App;

class Lang
{
    private static array $strings = [];
    private static string $current = 'id';

    public static function load(string $lang): void
    {
        $lang = in_array($lang, ['id', 'en']) ? $lang : 'id';
        self::$current = $lang;
        $file = dirname(__DIR__) . '/lang/' . $lang . '.php';
        self::$strings = file_exists($file) ? require $file : [];
    }

    public static function get(string $key, array $replace = []): string
    {
        $str = self::$strings[$key] ?? $key;
        foreach ($replace as $k => $v) {
            $str = str_replace(':' . $k, $v, $str);
        }
        return $str;
    }

    public static function current(): string
    {
        return self::$current;
    }

    public static function other(): string
    {
        return self::$current === 'id' ? 'en' : 'id';
    }
}
