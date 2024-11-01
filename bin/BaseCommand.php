<?php

namespace Aurora\Bin;

use Symfony\Component\Console\Command\Command;

class BaseCommand extends Command
{
    protected array $config = [];

    public function __construct()
    {
        $this->config = require(__DIR__ . '/../app/bootstrap/config.php');
        parent::__construct();
    }

    protected function getThemeFiles()
    {
        $theme = $this->config['db']->query('SELECT value FROM settings WHERE `key` = "theme"')->fetch()['value'];
        $absolute_theme_dir = \Aurora\Core\Helper::getPath($this->config['views'] . "/themes/$theme");
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
