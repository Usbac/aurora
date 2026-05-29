<?php

namespace Aurora\App;

final class Update
{
    private const UPDATE_DIRECTORIES = [
        'app/bootstrap',
        'app/controllers',
        'app/core',
        'app/database',
        'app/languages',
        'app/public',
        'app/react',
        'app/views',
        'bin',
        'docs',
        'tests',
        '.htaccess',
        'aurora',
        'composer.json',
    ];
    public const ERROR_CONNECTION = 1;
    public const ERROR_ZIP = 2;
    public const ERROR_COPY = 3;
    public const ERROR_BUILD = 4;

    /**
     * Updates the system to the given release zip
     * @param string $zip path to the release zip file
     * @param callable|null $on_build_output optional callback invoked with each line of React build output
     * @return int|bool true on success, an error code otherwise
     */
    public function run(string $zip, ?callable $on_build_output = null): int|bool
    {
        $temp = sys_get_temp_dir();
        $zip_file = tempnam($temp, 'aurora-update');

        if (!file_put_contents($zip_file, fopen($zip, 'r', false, self::getStreamContext()))) {
            return self::ERROR_CONNECTION;
        }

        $archive = new \ZipArchive();
        if ($archive->open($zip_file) !== true || !$archive->extractTo($temp) || !($index = $archive->getNameIndex(0))) {
            @unlink($zip_file);
            return self::ERROR_ZIP;
        }

        $archive->close();
        @unlink($zip_file);

        $root = \Aurora\Core\Helper::getPath();
        $update = "$temp/" . trim($index, '/');
        $backup = "$temp/" . uniqid('aurora-backup-');
        mkdir($backup);

        foreach (self::UPDATE_DIRECTORIES as $dir) {
            if (file_exists("$root/$dir") && !\Aurora\Core\Helper::copy("$root/$dir", "$backup/$dir")) {
                $this->removeDir($backup);
                return self::ERROR_COPY;
            }
        }

        foreach (self::UPDATE_DIRECTORIES as $dir) {
            if (!\Aurora\Core\Helper::copy("$update/$dir", "$root/$dir")) {
                $this->restore($backup, $root);
                $this->removeDir($backup);
                return self::ERROR_COPY;
            }
        }

        if (!$this->buildReact($on_build_output)) {
            $this->restore($backup, $root);
            $this->removeDir($backup);
            return self::ERROR_BUILD;
        }

        $this->removeDir($backup);
        return true;
    }

    private function buildReact(?callable $on_output = null): bool
    {
        $react_dir = \Aurora\Core\Helper::getPath('app/react');

        if (!is_file("$react_dir/package.json")) {
            return true;
        }

        $output = [];
        $return_var = 0;
        exec('npm --prefix "' . $react_dir . '" install && npm --prefix "' . $react_dir . '" run build 2>&1', $output, $return_var);

        if ($on_output && $output) {
            foreach ($output as $line) {
                $on_output($line);
            }
        }

        return $return_var === 0;
    }

    private function restore(string $backup, string $root): void
    {
        foreach (self::UPDATE_DIRECTORIES as $dir) {
            if (file_exists("$backup/$dir")) {
                \Aurora\Core\Helper::copy("$backup/$dir", "$root/$dir");
            }
        }
    }

    private function removeDir(string $dir): void
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

    /**
     * Returns an array with data about the latest release compatible with the current version (same major version)
     * @return array|bool|int the array with data about the latest release,
     * false if there are no new releases compatible with the current version
     * or an error code in case of errors
     */
    public function getLatestRelease(): array|bool|int
    {
        $releases = @file_get_contents('https://api.github.com/repos/usbac/aurora/releases', false, self::getStreamContext());

        if (!$releases) {
            return self::ERROR_CONNECTION;
        }

        $current_version = explode('.', \Aurora\Core\Kernel::VERSION);
        $latest_release = [];

        foreach (json_decode($releases, true) as $release) {
            $version = explode('.', trim($release['tag_name'], 'v'));

            // Ignore different major versions
            if ($version[0] != $current_version[0]) {
                continue;
            }

            $version_index = $this->getVersionIndex($version);
            if ($version_index >= ($latest_release['index'] ?? 0)) {
                $latest_release = [
                    'zip' => $release['zipball_url'],
                    'version' => $version,
                    'index' => $version_index,
                ];
            }
        }

        return empty($latest_release) || $this->getVersionIndex($current_version) >= $this->getVersionIndex($latest_release['version'])
            ? false
            : [
                'zip' => $latest_release['zip'],
                'version' => implode('.', $latest_release['version']),
            ];
    }

    /**
     * Returns the stream context for the requests
     * @return resource the stream context
     */
    private function getStreamContext()
    {
        return stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [ 'User-Agent: PHP' ],
            ],
        ]);
    }

    /**
     * Returns the index of the given version. Usually used when comparing versions.
     * @param array $version the version array
     * @return int the version index
     */
    private function getVersionIndex(array $version): int
    {
        return ($version[0] * 1000000) + ($version[1] * 1000) + ($version[2] ?? 0);
    }
}
