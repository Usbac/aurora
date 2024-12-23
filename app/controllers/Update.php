<?php

namespace Aurora\App;

final class Update
{
    private const UPDATE_DIRECTORIES = [
        'app/bootstrap/index.php',
        'app/bootstrap/routes.php',
        'app/controllers',
        'app/database',
        'app/languages',
        'app/views',
        'bin',
        'core',
        'docs',
        'public',
        'tests',
        '.htaccess',
        'aurora',
        'composer.json',
    ];
    public const ERROR_CONNECTION = 1;
    public const ERROR_ZIP = 2;
    public const ERROR_COPY = 3;

    /**
     * Updates the system to the given release zip
     * @param string path to the release zip file
     * @return int|bool true on success, an error code otherwise
     */
    public function run(string $zip): int|bool
    {
        $temp_dir = sys_get_temp_dir();
        $zip_dir = tempnam($temp_dir, 'aurora-update');

        if (!file_put_contents($zip_dir, fopen($zip, 'r', false, self::getStreamContext()))) {
            return self::ERROR_CONNECTION;
        }

        $zip = new \ZipArchive();

        if ($zip->open($zip_dir) !== true || !$zip->extractTo($temp_dir) || !($index = $zip->getNameIndex(0))) {
            return self::ERROR_ZIP;
        }

        $new_version_dir = "$temp_dir/" . trim($index, '/');

        if (!$zip->close()) {
            return self::ERROR_ZIP;
        }

        foreach (self::UPDATE_DIRECTORIES as $dir) {
            if (!\Aurora\Core\Helper::copy("$new_version_dir/$dir", \Aurora\Core\Helper::getPath("/$dir"))) {
                return self::ERROR_COPY;
            }
        }

        return true;
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
