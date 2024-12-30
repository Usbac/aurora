<?php

$db_folder = \Aurora\Core\Helper::getPath('app/database');
$db_file = "$db_folder/db.sqlite";
$db_exists = file_exists($db_file);
if (!$db_exists) {
    file_put_contents($db_file, '');
}

$db = new \Aurora\Core\DB("sqlite:$db_file");

if (!$db_exists) {
    (new \Aurora\App\Migration($db))->import(json_decode(file_get_contents("$db_folder/fixtures.json"), true)['tables']);
}

return [
    'bootstrap' => require(__DIR__ . '/index.php'),
    'date_format' => 'dd MMM Y HH:mm',
    'db'        => $db,
    'content'   => 'public/content',
    'per_page'  => 20,
    'mail'      => fn($to, $subject, $message) => mail($to, $subject, $message),
    'views'     => 'app/views',
];
