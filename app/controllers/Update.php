<?php

namespace Aurora\App;

final class Update
{
    private const UPDATE_DIRECTORIES = [
        'app',
        'bin',
        'bootstrap/index.php',
        'bootstrap/routes.php',
        'database',
        'docs',
        'public',
        'system',
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
     * @return true|int true on success, an error code otherwise
     */
    public function run(string $zip): true|int
    {
        if (!file_put_contents('update.zip', fopen($zip, 'r'))) {
            return self::ERROR_CONNECTION;
        }

        $zip = new \ZipArchive();

        if ($zip->open('update.zip') !== true || !$zip->extractTo('.') || !($index = $zip->getNameIndex(0))) {
            return self::ERROR_ZIP;
        }

        $zip_dir = trim($index, '/');

        if (!$zip->close()) {
            return self::ERROR_ZIP;
        }

        foreach (self::UPDATE_DIRECTORIES as $dir) {
            if (!\Aurora\System\Helper::copy("$zip_dir/$dir", dirname(__DIR__) . "/$dir")) {
                return self::ERROR_COPY;
            }
        }

        return true;
    }

    /**
     * Returns an array with data about the latest release compatible with the current version (same major version)
     * @return array|false|int the array with data about the latest release,
     * false if there are no new releases compatible with the current version
     * or an error code in case of errors
     */
    public function getLatestRelease(): array|false|int
    {
        $releases = @file_get_contents('https://api.github.com/repos/usbac/aurora/releases', false, stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [ 'User-Agent: PHP' ],
            ],
        ]));

        if (!$releases) {
            return self::ERROR_CONNECTION;
        }

        $current_version = explode('.', \Aurora\System\Kernel::VERSION);
        $latest_release = [];

        foreach (json_decode($releases, true) as $release) {
            $version = explode('.', trim($release['tag_name'], 'v'));

            // Ignore different major versions
            if ($version[0] != $current_version[0]) {
                continue;
            }

            $version_index = ($version[1] * 1000) + ($version[2] ?? 0);
            if ($version_index >= ($latest_release['index'] ?? 0)) {
                $latest_release = [
                    'zip' => $release['zipball_url'],
                    'version' => $version,
                    'index' => $version_index,
                ];
            }
        }

        return empty($latest_release)
            ? false
            : [
                'zip' => $latest_release['zip'],
                'version' => implode('.', $latest_release['version']),
            ];
    }
}
