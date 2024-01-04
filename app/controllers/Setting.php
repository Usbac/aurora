<?php

namespace Aurora\App;

final class Setting
{
    /**
     * Setting values
     * @var array
     */
    private static array $values = [];

    /**
     * Sets the setting values
     * @param array $values the setting values
     */
    public static function set(array $values): void
    {
        self::$values = $values;
    }

    /**
     * Returns the setting with the given key
     * @throws \InvalidArgumentException
     * @param [string|null] $key the setting key
     * @return mixed the setting with the given key, or all settings if the key is null
     */
    public static function get(?string $key = null): mixed
    {
        if (isset($key)) {
            if (array_key_exists($key, self::$values)) {
                return self::$values[$key];
            }

            throw new \InvalidArgumentException("Setting key '$key' does not exist");
        }

        return self::$values;
    }
}
