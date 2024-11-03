<?php

namespace Aurora\Tests;

class Base extends \PHPUnit\Framework\TestCase
{
    public function getDB(): \Aurora\Core\DB
    {
        $db_file = \Aurora\Core\Helper::getPath('tests/fixtures/db.sqlite');
        file_put_contents($db_file, '');

        $db = new \Aurora\Core\DB("sqlite:$db_file");

        (new \Aurora\App\Migration($db))->createSchema();

        return $db;
    }

    public function getLanguage(): \Aurora\Core\Language
    {
        $language = require(\Aurora\Core\Helper::getPath('app/languages/en.php'));
        $lang = new \Aurora\Core\Language([ 'en' => $language ]);
        $lang->setCode('en');

        return $lang;
    }
}
