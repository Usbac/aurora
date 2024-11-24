<?php

namespace Aurora\Core;

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
        return mb_substr($url, 0, mb_strpos($url, '?'));
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
     * Returns the true user ip or the string UNKNOWN if it's unknown
     * @return mixed the true user ip or the string UNKNOWN if it's unknown
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

        $dir_iterator = new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);
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

    /**
     * Returns the given byte size in a human readable format
     * @param float $bytes the size in bytes
     * @return string the byte size in a human readable format
     */
    public static function getByteSize(float $bytes): string
    {
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf('%.2f', $bytes / pow(1024, $factor)) . ([ 'B', 'kB', 'MB', 'GB', 'TB' ][$factor] ?? '');
    }

    /**
     * Returns the size in bytes based on the given PHP size string
     * @param string $size_str The PHP size string
     * @return int the size in bytes
     */
    public static function getPhpSize(string $size_str): int
    {
        $size = substr($size_str, 0, -1);
        switch (strtoupper(substr($size_str, -1))) {
            case 'P': $size *= 1024;
            case 'T': $size *= 1024;
            case 'G': $size *= 1024;
            case 'M': $size *= 1024;
            case 'K': $size *= 1024;
                break;
            default: $size = $size_str;
        }

        return (int) $size;
    }

    /**
     * Returns true if the given CSRF token is valid, false otherwise
     * @param string $value the CSRF token
     * @return bool true if the given CSRF token is valid, false otherwise
     */
    public static function isCsrfTokenValid(string $value): bool
    {
        return isset($_COOKIE['csrf_token']) && $_COOKIE['csrf_token'] === $value;
    }

    /**
     * Returns true if the given slug is valid
     * @param string $value the slug
     * @return bool true if the given slug is valid
     */
    public static function isSlugValid(string $value): bool
    {
        return preg_match('/^[a-zA-Z0-9\-_]+$/', $value) === 1;
    }

    /**
     * Downloads the file with the given path, filename, and content type
     * @param string $file_path the file path
     * @param string $filename the filename for the Content-Disposition header
     * @param string $content_type the content type
     * @return int|false the number of bytes read from the file, or false on failure
     */
    public static function downloadFile(string $file_path, string $filename, string $content_type): int|false
    {
        $file_exists = file_exists($file_path);
        header("Content-Type: $content_type");
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Content-Length: ' . ($file_exists ? filesize($file_path) : 0));
        header('Pragma: public');
        flush();

        return $file_exists ? readfile($file_path) : false;
    }
}
