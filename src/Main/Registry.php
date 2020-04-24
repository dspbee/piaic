<?php
namespace Piaic\Main;

use Piaic\Request\Request;

/**
 * Class Registry
 * @package Piaic\Main
 */
class Registry
{
    /**
     * @param string $key
     * @param mixed $value
     */
    public static function set(string $key, $value)
    {
        self::$storage[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function exist(string $key): bool
    {
        return array_key_exists($key, self::$storage);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public static function get(string $key)
    {
        return self::$storage[$key] ?? null;
    }

    /**
     * @param string $key
     */
    public static function remove(string $key)
    {
        if (array_key_exists($key, self::$storage)) {
            unset(self::$storage[$key]);
        }
    }

    public static function request(): Request
    {
        return self::$storage['__request__'];
    }

    private static $storage = [];
}