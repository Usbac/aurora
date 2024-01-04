<?php

namespace Aurora\App;

final class Media
{
    private const FOLDER_PERMISSION = 0755;

    /**
     * The content directory relative to the project root directory
     * @var string
     */
    private static string $directory = '';

    /**
     * Sets the content directory
     * @param string $directory the content directory relative to the project root directory
     */
    public static function setDirectory(string $directory): void
    {
        self::$directory = $directory;
    }

    /**
     * Returns the files and folders in the given path
     * @throws \InvalidArgumentException
     * @param string $path the path relative to the project root directory
     * @param [string] $search the search string
     * @param [string] $order the order (name, type, size)
     * @return array the files and folders in the given path
     */
    public static function getFiles(string $path, string $search = '', string $order = 'name'): array
    {
        $path = \Aurora\System\Helper::getPath($path);

        if (!self::isValidPath($path)) {
            throw new \InvalidArgumentException("Path '$path' is not a valid path within " . self::$directory);
        }

        $content_path_length = strlen(\Aurora\System\Helper::getPath(self::$directory));

        $files = array_map(function($file) use ($content_path_length) {
            $mime = mime_content_type($file);
            return [
                'name'     => basename($file),
                'path'     => substr($file, $content_path_length),
                'mime'     => $mime,
                'is_file'  => is_file($file),
                'is_image' => str_starts_with($mime, 'image/'),
                'size'     => filesize($file),
                'time'     => filemtime($file),
            ];
        }, glob("$path/*"));

        if (!empty($search)) {
            $files = array_filter($files, fn($file) => stripos($file['name'], $search) !== false);
        }

        match ($order) {
            'name' => usort($files, fn($a, $b) => strnatcmp($a['name'], $b['name'])),
            'type' => usort($files, fn($a, $b) => $b['is_file'] <=> $a['is_file']),
            'size' => usort($files, fn($a, $b) => $b['size'] <=> $a['size']),
        };

        return [
            ...array_filter($files, fn($file) => !$file['is_file']),
            ...array_filter($files, fn($file) => $file['is_file']),
        ];
    }

    /**
     * Creates a new folder with the given name in the given path
     * @throws \InvalidArgumentException
     * @param string $path the path relative to the project root directory
     * @param string $name the folder name
     * @return bool true if the folder was created successfully, false otherwise
     */
    public static function addFolder(string $path, string $name): bool
    {
        $path = \Aurora\System\Helper::getPath($path);

        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Folder name is empty');
        }

        if (!self::isValidPath($path)) {
            throw new \InvalidArgumentException("Path '$path' is not a valid path within " . self::$directory);
        }

        return mkdir("$path/$name", self::FOLDER_PERMISSION);
    }

    /**
     * Deletes the file/folder with given path
     * @throws \InvalidArgumentException
     * @param string $path the path relative to the project root directory
     * @return bool true if the file/folder was deleted successfully, false otherwise
     */
    public static function remove(string $path): bool
    {
        $path = \Aurora\System\Helper::getPath($path);

        if (!self::isValidPath($path)) {
            throw new \InvalidArgumentException("Path '$path' is not a valid path within " . self::$directory);
        }

        return self::removeFile($path);
    }

    /**
     * Renames the file/folder with given path
     * @throws \InvalidArgumentException
     * @param string $path the path relative to the project root directory
     * @param string $name the new name
     * @return bool true if the file/folder was renamed successfully, false otherwise
     */
    public static function rename(string $path, string $name): bool
    {
        $path = \Aurora\System\Helper::getPath($path);

        if (!self::isValidPath($path)) {
            throw new \InvalidArgumentException("Path '$path' is not a valid path within " . self::$directory);
        }

        return rename($path, dirname($path) . "/$name");
    }

    /**
     * Moves the file/folder with given path to the given folder
     * @throws \InvalidArgumentException
     * @param string $path the path relative to the project root directory
     * @param string $name the destination folder relative to the project root directory
     * @return bool true if the file/folder was moved successfully, false otherwise
     */
    public static function move(string $path, string $folder): bool
    {
        $path = \Aurora\System\Helper::getPath($path);
        $folder = \Aurora\System\Helper::getPath($folder);

        if (!self::isValidPath($path)) {
            throw new \InvalidArgumentException("Path '$path' is not a valid path within " . self::$directory);
        }

        if (!self::isValidPath($folder)) {
            throw new \InvalidArgumentException("Path '$folder' is not a valid path within " . self::$directory);
        }

        return $folder == $path || rename($path, $folder . '/' . basename($path));
    }

    /**
     * Duplicates the file with given path with the given name
     * @throws \InvalidArgumentException
     * @param string $path the path relative to the project root directory
     * @param string $name the new name
     * @return bool true if the file was duplicated successfully, false otherwise
     */
    public static function duplicate(string $path, string $name): bool
    {
        $source = \Aurora\System\Helper::getPath($path);
        $destination = dirname($source) . "/$name";

        if (!self::isValidPath($source)) {
            throw new \InvalidArgumentException("Path '$source' is not a valid path within " . self::$directory);
        }

        if (!self::isValidPath($destination)) {
            throw new \InvalidArgumentException("Path '$destination' is not a valid path within " . self::$directory);
        }

        return \Aurora\System\Helper::copy($source, $destination);
    }

    /**
     * Returns true if the given path is a valid content path, false otherwise
     * @param string $path the path
     * @return bool true if the given path is a valid content path, false otherwise
     */
    public static function isValidPath(string $path): bool
    {
        $content_dir = \Aurora\System\Helper::getPath(self::$directory);

        return $path !== '' && strncmp($path, $content_dir, strlen($content_dir)) === 0;
    }

    /**
     * Returns the file size in a human readable format
     * @param float $bytes the file size in bytes
     * @return string the file size in a human readable format
     */
    public static function getFileSize(float $bytes): string
    {
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf('%.2f', $bytes / pow(1024, $factor)) . ([ 'B', 'kB', 'MB', 'GB', 'TB' ][$factor] ?? '');
    }

    /**
     * Uploads the given file to the given path
     * @param array $file the file
     * @param string $path the path relative to the project root directory
     * @return bool true if the file was uploaded successfully, false otherwise
     */
    public static function uploadFile($file, string $path): bool
    {
        $path = \Aurora\System\Helper::getPath($path);
        $container_path = substr($path, 0, strrpos($path, '/') + 1);

        if (!$file) {
            throw new \InvalidArgumentException('File is empty');
        }

        if (!self::isValidPath($path)) {
            throw new \InvalidArgumentException("Path '$path' is not a valid path within " . self::$directory);
        }

        if (!file_exists($container_path)) {
            mkdir($container_path, self::FOLDER_PERMISSION, true);
        }

        return move_uploaded_file($file['tmp_name'], self::getFilePath($path, $file['name']));
    }

    /**
     * Returns the maximum upload file size
     * @return mixed the maximum upload file size
     */
    public static function getMaxUploadFileSize(): mixed
    {
        return min(self::getPHPSize(ini_get('post_max_size')), self::getPHPSize(ini_get('upload_max_filesize')));
    }

    /**
     * Removes the given file|directory recursively
     * @param string $dir the file|directory path
     * @return bool true if the file|directory has been deleted
     * or if the file|directory doesn't exists, false otherwise
     */
    private static function removeFile(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (glob("$dir/{,.}*[!.]*", GLOB_MARK | GLOB_BRACE) as $file) {
            if (is_dir($file)) {
                self::removeFile($file);
            } else {
                unlink($file);
            }
        }

        return rmdir($dir);
    }

    /**
     * Returns the right path for a file
     * This function handles duplicates.
     * The suffix " (n)" will be appended to the file's name in case of duplicates
     * @param string $path the file path
     * @param string $filename the file name
     * @return string the right path for the file
     */
    private static function getFilePath(string $path, string $filename): string
    {
        $file_path = "$path/$filename";

        if (!is_file($file_path)) {
            return $file_path;
        }

        $info = pathinfo($file_path);
        $ext = $info['extension'] ? ('.' . $info['extension']) : $info['extension'];

        $i = 1;
        while (file_exists($file_path = $info['dirname'] . '/' . $info['filename'] . " ($i)$ext")) {
            $i++;
        }

        return $file_path;
    }

    /**
     * Returns the size in bytes based on the given PHP size string
     * @param string $size_str The PHP size string
     * @return int the size in bytes
     */
    private static function getPHPSize(string $size_str): int
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
}
