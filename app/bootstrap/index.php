<?php

if (!function_exists('e')) {
    function e($val): string
    {
        return htmlspecialchars($val, ENT_QUOTES);
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
    $db = $kernel->config('db');
    $settings = $db->query('SELECT `key`, value FROM settings')->fetchAll(\PDO::FETCH_KEY_PAIR);

    header('X-Content-Type-Options: nosniff');
    ini_set('session.cookie_httponly', 1);
    ini_set('error_log', \Aurora\Core\Helper::getPath($settings['log_file']));
    ini_set('display_errors', $settings['display_errors'] ? 1 : 0);
    ini_set('display_startup_errors', $settings['display_errors'] ? 1 : 0);
    error_reporting($settings['log_errors'] ? E_ALL : 0);
    date_default_timezone_set($settings['timezone']);
    session_set_cookie_params([
        'lifetime' => (int) $settings['session_lifetime'],
        'samesite' => $settings['samesite_cookie'],
    ]);
    session_start();

    $languages = [];
    foreach (glob(\Aurora\Core\Helper::getPath('app/languages/*.php')) as $file) {
        $languages[pathinfo($file, PATHINFO_FILENAME)] = require_once($file);
    }

    $lang = new \Aurora\Core\Language($languages, $settings['language']);

    $view = new \Aurora\Core\View(\Aurora\Core\Helper::getPath($kernel->config('views')), new \Aurora\App\ViewHelper($kernel->config('date_format'), $lang));

    \Aurora\App\Permission::set($db->query('SELECT permission, role_level FROM roles_permissions ORDER BY permission')->fetchAll(\PDO::FETCH_KEY_PAIR), $GLOBALS['user']['role']?? 0);
    \Aurora\App\Permission::addMethod('impersonate', fn($user) => ($user['status'] ?? false) && $user['role'] <= ($GLOBALS['user']['role'] ?? 0) && \Aurora\App\Permission::can('impersonate'));
    \Aurora\App\Permission::addMethod('edit_user', fn($user) => ($user['role'] ?? 0) <= ($GLOBALS['user']['role'] ?? 0) && \Aurora\App\Permission::can('edit_users'));
    \Aurora\App\Setting::set($settings);
    \Aurora\App\Media::setDirectory($kernel->config('content'));

    (require('routes.php'))($kernel, $db, $view, $lang);
};
