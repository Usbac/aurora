<?php

namespace Aurora\App;

final class Permission
{
    /**
     * List of permissions
     * @var array
     */
    private static array $permissions = [];

    /**
     * User role
     * @var int
     */
    private static int $user_role = 0;

    /**
     * List of methods
     * @var array
     */
    private static array $methods = [];

    public static function __callStatic($name, $args)
    {
        if (!array_key_exists($name, self::$methods)) {
            throw new \BadMethodCallException("Method '$name' does not exist");
        }

        return call_user_func_array(self::$methods[$name], $args);
    }

    /**
     * Adds a static method
     * @param string $name the method name
     * @param callable $func the method
     */
    public static function addMethod(string $name, callable $func): void
    {
        self::$methods[$name] = $func;
    }

    /**
     * Sets the permissions
     * @param array $permissions the permissions
     * @param int $user_role the user role
     */
    public static function set(array $permissions, int $user_role): void
    {
        self::$permissions = $permissions;
        self::$user_role = $user_role;
    }

    /**
     * Returns true if the current user has the permission with the given key, false otherwise
     * @throws \InvalidArgumentException
     * @param string $key the permission key
     * @return bool true if the current user has the permission with the given key, false otherwise
     */
    public static function can(string $key): bool
    {
        if (!array_key_exists($key, self::$permissions)) {
            throw new \InvalidArgumentException("Permission '$key' does not exist");
        }

        return self::$user_role >= self::$permissions[$key];
    }

    /**
     * Returns the list of permission keys
     * @return array the list of permission keys
     */
    public static function getPermissions(): array
    {
        return array_keys(self::$permissions);
    }
}
