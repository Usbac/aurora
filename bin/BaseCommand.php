<?php

namespace Aurora\Bin;

use Symfony\Component\Console\Command\Command;

class BaseCommand extends Command
{
    protected array $config = [];
    protected array $settings = [];

    public function __construct()
    {
        $this->config = require(__DIR__ . '/../app/bootstrap/config.php');
        $this->settings = $this->config['db']->query('SELECT * FROM settings')->fetchAll(\PDO::FETCH_KEY_PAIR);
        date_default_timezone_set($this->settings['timezone']);
        parent::__construct();
    }

    protected function getThemeFiles()
    {
        $absolute_theme_dir = \Aurora\Core\Helper::getPath($this->config['views'] . '/themes/' . $this->settings['theme']);
        $view_files = [ '' ];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($absolute_theme_dir)) as $file) {
            if ($file->isFile()) {
                $view_files[] = mb_substr($file->getPathname(), mb_strlen($absolute_theme_dir) + 1);
            }
        }

        natcasesort($view_files);

        return array_values($view_files);
    }
}
