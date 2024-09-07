<?php

if (!function_exists('e')) {
    function e($val): string
    {
        return htmlspecialchars($val, ENT_QUOTES);
    }
}

if (!function_exists('t')) {
    function t(?string $key = null, bool $escape = true)
    {
        $text = \Aurora\Core\Container::get('language')->get($key);
        return $key && $escape ? e($text) : $text;
    }
}

if (!function_exists('js')) {
    function js(mixed $val): string|bool
    {
        return json_encode($val);
    }
}

if (!function_exists('setting')) {
    function setting(?string $key = null): mixed
    {
        return \Aurora\App\Setting::get($key);
    }
}

return function (\Aurora\Core\Kernel $kernel) {
    $languages = [];
    foreach (glob(\Aurora\Core\Helper::getPath('app/languages/*.php')) as $file) {
        $languages[pathinfo($file, PATHINFO_FILENAME)] = require_once($file);
    }

    $db = $kernel->config('db');
    $lang = new \Aurora\Core\Language($languages);
    $view = new \Aurora\Core\View(\Aurora\Core\Helper::getPath(\Aurora\Core\Kernel::config('views')), new \Aurora\App\ViewHelper());
    $settings = $db->query('SELECT `key`, value FROM settings')->fetchAll(\PDO::FETCH_KEY_PAIR);
    $permissions = $db->query('SELECT permission, role_level FROM roles_permissions ORDER BY permission')->fetchAll(\PDO::FETCH_KEY_PAIR);

    $lang->setCode($settings['language']);

    \Aurora\Core\Container::set('language', $lang);
    \Aurora\App\Permission::set($permissions, $_SESSION['user']['role'] ?? 0);
    \Aurora\App\Permission::addMethod('impersonate', fn($user) => ($user['status'] ?? false) && $user['role'] <= ($_SESSION['user']['role'] ?? 0) && \Aurora\App\Permission::can('impersonate'));
    \Aurora\App\Setting::set($settings);
    \Aurora\App\Media::setDirectory(\Aurora\Core\Kernel::config('content'));

    (require('routes.php'))($kernel->router, $db, $view, $lang);
};
