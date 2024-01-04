<?php

namespace Aurora\System;

final class Helper
{
    /**
     * Returns the given path relative to the project root directory
     * @param string $path the path
     * @return string the given path relative to the project root directory
     */
    public static function getPath(string $path = ''): string
    {
        return dirname(__DIR__) . (empty($path) ? '' : '/' . ltrim($path, '/'));
    }

    /**
     * Returns the current path without parameters
     * @return string the current path
     */
    public static function getCurrentPath(): string
    {
        $url = trim($_GET['url'] ?? '', '/') . '?';
        return substr($url, 0, strpos($url, '?'));
    }

    /**
     * Returns the full url of the given path
     * @param [string] $path the path
     * @return string the full url
     */
    public static function getUrl(string $path = ''): string
    {
        $path = ltrim($path, '/');
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;

        return 'http' . ($https ? 's' : '') . '://' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . (empty($path) ? '' : "/$path");
    }

    /**
     * Returns true if the given id is valid, false otherwise
     * @param mixed $id the id
     * @return bool true if the given id is valid, false otherwise
     */
    public static function isValidId(mixed $id): bool
    {
        return is_numeric((string) $id);
    }

    /**
     * Returns the true user ip
     * @return mixed the true user ip
     */
    public static function getUserIP(): mixed
    {
        foreach ([
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ] as $key) {
            if (filter_var($_SERVER[$key] ?? null, FILTER_VALIDATE_IP)) {
                return $_SERVER[$key];
            }
        }

        return 'UNKNOWN';
    }

    /**
     * Copies the given source (file or directory) to the given destination
     * @param string $source the source
     * @param string $destination the destination
     * @param [int] $permission the permission
     * @return bool true if the given source was copied to the given destination, false otherwise
     */
    public static function copy(string $source, string $destination, int $permission = 0755): bool
    {
        if (is_file($source)) {
            return copy($source, $destination);
        }

        if (!is_dir($destination)) {
            mkdir($destination, $permission);
        }

        $directory_iterator = new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory_iterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            $new_path = "$destination/" . call_user_func([ $iterator, 'getSubPathname' ]);
            $res = $item->isDir()
                ? (is_dir($new_path) || mkdir($new_path, $permission))
                : copy($item, $new_path);

            if (!$res) {
                return false;
            }
        }

        return true;
    }
}
