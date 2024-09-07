<?php

namespace Aurora\Core;

final class Container
{
    /**
     * List of services
     * @var array
     */
    public static array $services = [];

    /**
     * Returns the service with the given key
     * @throws \InvalidArgumentException
     * @param string $key the service key
     * @param [array] $args the arguments for the service
     * @return mixed the service
     */
    public static function get(string $key, array $args = []): mixed
    {
        if (!array_key_exists($key, self::$services)) {
            throw new \InvalidArgumentException("Service '$key' does not exist");
        }

        return is_callable(self::$services[$key])
            ? call_user_func_array(self::$services[$key], $args)
            : self::$services[$key];
    }

    /**
     * Sets a service
     * @param string $key the service key
     * @param mixed $value the service value
     */
    public static function set(string $key, $value): void
    {
        self::$services[$key] = $value;
    }
}
