<?php

namespace Aurora\Core;

final class Helper
{
    /**
     * Returns the given path relative to the app root directory
     * @param string $path the path
     * @return string the given path relative to the app root directory
     */
    public static function getPath(string $path = ''): string
    {
        return dirname(__DIR__) . (empty($path) ? '' : '/' . trim($path, '/'));
    }

    /**
     * Returns the given path relative to the project root directory
     * @param string $path the path
     * @return string the given path relative to the project root directory
     */
    public static function getProjectPath(string $path = ''): string
    {
        return dirname(self::getPath()) . (empty($path) ? '' : '/' . trim($path, '/'));
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
     * Returns the public path of the given content file
     * @param string|null $path the path relative to the content directory
     * @return string|null the public path
     */
    public static function getContentPath(?string $path = ''): ?string
    {
        if ($path === null || $path === '' || parse_url($path, PHP_URL_HOST)) {
            return $path;
        }

        $content = trim(\Aurora\Core\Kernel::config('content'), '/');
        $path = ltrim($path, '/');

        return $path === $content || str_starts_with($path, "$content/")
            ? "/$path"
            : "/$content/$path";
    }

    /**
     * Returns the full url of the given path
     * @param [string] $path the path
     * @return string the full url
     */
    public static function getUrl(string $path = ''): string
    {
        $path = ltrim($path, '/');
        return 'http' . (self::isHttps() ? 's' : '') . '://' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . (empty($path) ? '' : "/$path");
    }

    /**
     * Returns true if the current request is made via HTTPS, false otherwise
     * @return bool true if the current request is made via HTTPS, false otherwise
     */
    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
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
     * Returns the Authorization header value from the current request
     * @return string the Authorization header value, or an empty string if missing
     */
    public static function getAuthorizationHeader(): string
    {
        foreach ([ 'HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION' ] as $key) {
            if (!empty($_SERVER[$key])) {
                return $_SERVER[$key];
            }
        }

        $headers = function_exists('getallheaders') ? getallheaders() : [];

        if ($headers === false) {
            $headers = [];
        }

        if (function_exists('apache_request_headers')) {
            $headers = array_merge($headers, apache_request_headers() ?: []);
        }

        foreach ($headers as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0) {
                return $value;
            }
        }

        return '';
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
            mkdir(dirname($destination), $permission, true);
            return @copy($source, $destination);
        }

        if (!is_dir($destination) && !mkdir($destination, $permission, true)) {
            return false;
        }

        $iterator = self::getFileIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            $new_path = "$destination/" . call_user_func([ $iterator, 'getSubPathname' ]);
            $res = $item->isDir()
                ? (is_dir($new_path) || mkdir($new_path, $permission, true))
                : copy($item, $new_path);

            if (!$res) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns a recursive file iterator
     * @param string $path the path
     * @param [int] $flags the flags
     * @param [int] $mode the mode
     * @return \RecursiveIteratorIterator a recursive file iterator
     */
    public static function getFileIterator(string $path,
        int $flags = \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO,
        int $mode = \RecursiveIteratorIterator::LEAVES_ONLY,
    ): \RecursiveIteratorIterator
    {
        return new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, $flags), $mode);
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

    public static function getRequestData(): array
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !empty($_POST)) {
            return $_POST;
        }

        $raw_input = file_get_contents('php://input');

        if (empty($raw_input)) {
            return [];
        }

        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($content_type, 'application/json') !== false) {
            $decoded = json_decode($raw_input, true);
            return is_array($decoded) ? $decoded : [];
        }

        if (stripos($content_type, 'application/x-www-form-urlencoded') !== false) {
            parse_str($raw_input, $data);
            return $data;
        }

        return [ '_raw' => $raw_input ];
    }

    /**
     * Appends a message to the log file
     * @param string $message the message
     * @param string|null $path the log file path. Defaults to the configured log file path
     */
    public static function log(string $message, ?string $path = null): void
    {
        $path ??= ini_get('error_log');

        if (empty($path)) {
            return;
        }

        file_put_contents($path, sprintf("[%s UTC] %s\n", gmdate('d-M-Y H:i:s'), $message), FILE_APPEND | LOCK_EX);
    }

    /**
     * Removes the given directory recursively
     * @param string $dir the directory
     */
    public static function removeDirRecursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        ) as $item) {
            $item->isDir() ? rmdir($item) : unlink($item);
        }

        rmdir($dir);
    }
}
