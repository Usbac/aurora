<?php

use Aurora\Core\{DB, Helper, Kernel, Language, View};

return function (\Aurora\Core\Kernel $kernel, DB $db, View $view, Language $lang, ?array &$user) {
    $user_mod = new \Aurora\App\Modules\User($db, $lang);
    $tag_mod = new \Aurora\App\Modules\Tag($db, $lang);
    $link_mod = new \Aurora\App\Modules\Link($db, $lang);
    $page_mod = new \Aurora\App\Modules\Page($db, $lang);
    $post_mod = new \Aurora\App\Modules\Post($db, $lang);

    $blog_url = \Aurora\App\Setting::get('blog_url');
    $theme_dir = 'themes/' . \Aurora\App\Setting::get('theme');
    $rss = \Aurora\App\Setting::get('rss');
    $router = $kernel->router;

    $getAuthToken = function() {
        return preg_match('/Bearer\s(\S+)/i', Helper::getAuthorizationHeader(), $matches)
            ? $matches[1]
            : ($_COOKIE['auth_token'] ?? false);
    };

    $setAuthToken = function($token, $time) {
        return setcookie('auth_token',
            $token,
            [
                'expires' => $time,
                'path' => '/',
                'domain' => '',
                'secure' => \Aurora\Core\Helper::isHttps(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
    };

    $router->get([ 'admin', 'admin/*' ], function() use ($view) {
        return $view->get('admin.html');
    });

    /**
     * BLOG
     */

    $router->get('json:api/blog/posts', function() use ($view, $post_mod, $theme_dir) {
        $current_page = max(1, (int) ($_GET['page'] ?? 1));
        $per_page = \Aurora\App\Setting::get('per_page');
        $where = [ $post_mod->getCondition([ 'status' => 1 ]) ];

        if (!empty($_GET['user'])) {
            $where[] = 'posts.user_id = ' . ((int) $_GET['user']);
        }

        if (!empty($_GET['tag'])) {
            $where[] = 'posts.id IN (SELECT post_id FROM posts_to_tags WHERE tag_id = ' . ((int) $_GET['tag']) . ')';
        }

        $where = implode(' AND ', $where);

        return json_encode([
            'next_page' => $post_mod->isNextPageAvailable($current_page, $per_page, $where),
            'html' => $view->get("$theme_dir/partials/posts_page.html", [
                'posts' => $post_mod->getPage($current_page, $per_page, $where),
            ]),
        ]);
    });

    $router->get($blog_url, function() use ($db, $view, $lang, $link_mod, $post_mod, $theme_dir) {
        $current_page = max(1, (int) ($_GET['page'] ?? 1));
        $per_page = \Aurora\App\Setting::get('per_page');
        $where = $post_mod->getCondition([ 'status' => 1 ]);
        $search = $db->escape($_GET['search'] ?? '');

        if (!empty($search)) {
            $where .= " AND (posts.title LIKE '%$search%' OR posts.description LIKE '%$search%')";
        }

        return $view->get("$theme_dir/blog.html", [
            'header_links' => $link_mod->getHeaderLinks(),
            'title' => $lang->get('blog'),
            'posts' => $post_mod->getPage($current_page, $per_page, $where, 'date', false, true),
            'next_page' => $post_mod->isNextPageAvailable($current_page, $per_page, $where),
            'current_page' => $current_page,
        ]);
    });

    $router->get("$blog_url/author/{author}", function() use ($view, $user_mod, $link_mod, $post_mod, $theme_dir) {
        $current_page = max(1, (int) ($_GET['page'] ?? 1));
        $author = $user_mod->get([ 'slug' => $_GET['author'] ]);
        $per_page = \Aurora\App\Setting::get('per_page');

        if (!$author) {
            return [ '', 404 ];
        }

        $where = implode(' AND ', [
            $post_mod->getCondition([ 'status' => 1 ]),
            'users.id = ' . ((int) $author['id']),
        ]);

        return $view->get("$theme_dir/blog.html", [
            'header_links' => $link_mod->getHeaderLinks(),
            'title' => $author['name'],
            'user' => $author,
            'posts' => $post_mod->getPage($current_page, $per_page, $where, 'date', false, true),
            'next_page' => $post_mod->isNextPageAvailable($current_page, $per_page, $where),
            'current_page' => $current_page,
        ]);
    });

    $router->get("$blog_url/tag/{tag}", function() use ($view, $tag_mod, $link_mod, $post_mod, $theme_dir) {
        $current_page = max(1, (int) ($_GET['page'] ?? 1));
        $per_page = \Aurora\App\Setting::get('per_page');
        $tag = $tag_mod->get([ 'slug' => $_GET['tag'] ]);

        if (!$tag) {
            return [ '', 404 ];
        }

        $where = implode(' AND ', [
            $post_mod->getCondition([ 'status' => 1 ]),
            'posts.id IN (SELECT post_id FROM posts_to_tags WHERE tag_id = ' . ((int) $tag['id']) . ')',
        ]);

        return $view->get("$theme_dir/blog.html", [
            'header_links' => $link_mod->getHeaderLinks(),
            'title' => $tag['name'],
            'tag' => $tag,
            'posts' => $post_mod->getPage($current_page, $per_page, $where, 'date', false, true),
            'next_page' => $post_mod->isNextPageAvailable($current_page, $per_page, $where),
            'current_page' => $current_page,
        ]);
    });

    $router->get("$blog_url/{slug}", function() use ($db, $view, $link_mod, $post_mod, $theme_dir, &$user) {
        $post_cond = $post_mod->getCondition([ 'status' => 1 ]);

        $post = $post_mod->get([
            'slug' => $_GET['slug'] ?? '',
            empty($user) ? $post_cond : '',
        ]);

        if (!$post) {
            return [ '', 404 ];
        }

        if (\Aurora\App\Setting::get('views_count')) {
            $db->replace('views', [
                'type' => 'post',
                'item_id' => $post['id'],
                'ip' => Helper::getUserIP(),
                'date' => time(),
            ]);
        }

        return $view->get("$theme_dir/post.html", [
            'header_links' => $link_mod->getHeaderLinks(),
            'post' => $post,
            'related_posts' => empty($post['tags_id'])
                ? []
                : $post_mod->getPage(1, 3, "$post_cond AND p2t.tag_id IN (" . $post['tags_id'] . ') AND posts.id != ' . $post['id']),
        ]);
    });

    if (!empty($rss)) {
        $router->get("xml:$rss", function() use ($post_mod, $view, $theme_dir) {
            return $view->get("$theme_dir/rss.html", [
                'posts' => $post_mod->getPage(null, null, $post_mod->getCondition([ 'status' => 1 ]), 'date', false),
            ]);
        });
    }

    $router->get([ '/', '{slug}' ], function() use ($db, $view, $link_mod, $page_mod, $theme_dir, &$user) {
        $page = $page_mod->get([
            'slug' => $_GET['slug'] ?? '',
            empty($user) ? $page_mod->getCondition([ 'status' => 1 ]) : '',
        ]);

        if (!$page) {
            return [ '', 404 ];
        }

        if (\Aurora\App\Setting::get('views_count')) {
            $db->replace('views', [
                'type' => 'page',
                'item_id' => $page['id'],
                'ip' => Helper::getUserIP(),
                'date' => time(),
            ]);
        }

        $template = !empty($page['static']) && !empty($page['static_file'])
            ? $page['static_file']
            : 'page.html';

        return $view->get("$theme_dir/$template", [
            'header_links' => $link_mod->getHeaderLinks(),
            ...$page,
        ]);
    });

    $login = function($user_id) use ($db, $setAuthToken) {
        $token = bin2hex(random_bytes(64));

        try {
            $success = (bool) $db->insert('tokens', [
                'user_id' => $user_id,
                'token' => $token,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip' => Helper::getUserIP(),
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        } catch (\Exception) {
            return [
                'success' => false,
                'error' => 'error_generic',
            ];
        }

        if ($success) {
            $total = (int) $db->query('SELECT COUNT(*) FROM tokens WHERE user_id = ?', $user_id)->fetchColumn();
            $to_remove = (int) ($total - \Aurora\Core\Kernel::config('max_active_sessions'));
            if ($to_remove > 0) {
                $db->query('DELETE FROM tokens WHERE user_id = ? ORDER BY created_at ASC, token ASC LIMIT ?', $user_id, $to_remove);
            }

            $setAuthToken($token, time() + (60 * 60 * 24 * 30)); // 30 days
        }

        return [ 'success' => $success ];
    };

    /**
     * NEW
     */

    $router->code(404, function() use ($view, $lang, $theme_dir) {
        return $view->get("$theme_dir/information.html", [
            'title' => '404',
            'description' => $lang->get('not_found'),
            'subdescription' => $lang->get('not_found_desc'),
        ]);
    });

    $router->middleware('*', function() use ($db, $view, $lang, $theme_dir, $user_mod, $getAuthToken, &$user) {
        $token = $getAuthToken();
        $user = $user_mod->get([
            'id' => $db->query('SELECT user_id FROM tokens WHERE token = ?', $token)->fetchColumn(),
            'status' => 1,
        ]) ?: null;

        if ($user) {
            $user['token'] = $token;
        }

        \Aurora\App\Permission::set($db->query('SELECT permission, role_level FROM roles_permissions ORDER BY permission')->fetchAll(\PDO::FETCH_KEY_PAIR), $user['role'] ?? 0);

        if (\Aurora\App\Setting::get('maintenance') && !str_starts_with(Helper::getCurrentPath(), 'admin') && !str_starts_with(Helper::getCurrentPath(), 'api') && !Helper::isValidId($user['id'] ?? false)) {
            echo $view->get("$theme_dir/information.html", [
                'description' => $lang->get('under_maintenance'),
                'subdescription' => $lang->get('come_back_soon'),
            ]);
            exit;
        }
    });

    $router->post('json:api/password-reset/request', function($body) use ($db, $lang, $user_mod, $view) {
        $hash = bin2hex(random_bytes(18));
        $user = $user_mod->get([
            'email' => $body['email'],
            'status' => 1,
        ]);

        if ($user) {
            $db->replace('password_restores', [
                'user_id' => $user['id'],
                'hash' => $hash,
                'created_at' => time(),
            ]);
            \Aurora\Core\Kernel::config('mail')($user['email'], $lang->get('restore_your_password'), $view->get('emails/password_restore.html', [ 'hash' => $hash ]));
        }

        return json_encode([ 'success' => true ]);
    });

    $router->post('json:api/password-reset/confirm', function($body) use ($db, $user_mod, $login) {
        $hash = $body['hash'] ?? '';
        $password = $body['password'] ?? '';
        $restore = $db->query('SELECT * FROM password_restores WHERE hash = ?', $hash)->fetch();

        if (empty($restore) || $restore['created_at'] < strtotime('-2 hours')) {
            return json_encode([
                'success' => false,
                'error' => 'expired_restore',
            ]);
        }

        $error = $user_mod->checkPassword($password, $body['password_confirm'] ?? '');
        if (empty($error)) {
            $user = $user_mod->get([
                'id' => $restore['user_id'],
                'status' => 1,
            ]);

            if (!$user) {
                return json_encode([
                    'success' => false,
                    'error' => 'no_active_user',
                ]);
            }

            $db->delete('password_restores', $hash, 'hash');
            $db->query('DELETE FROM tokens WHERE user_id = ?', $user['id']);
            $db->update($user_mod->getTable(), [ 'password' => $user_mod->getPassword($password) ], $user['id']);
            return json_encode($login($user['id']));
        }

        return json_encode([
            'success' => false,
            'error' => $error,
        ]);
    });

    $router->middleware('api/*', function() use ($db, &$user) {
        $path = Helper::getCurrentPath();

        if (str_starts_with($path, 'api/blog')) {
            return;
        }

        if (empty($user) && !in_array($path, [ 'api/auth', 'api/password-reset/request', 'api/password-reset/confirm', 'api/logout' ])) {
            return [ '', 401 ];
        }

        if (!empty($user['id'])) {
            $now = time();
            $db->query('UPDATE tokens
                SET updated_at = ?, user_agent = ?
                WHERE token = ?', $now, $_SERVER['HTTP_USER_AGENT'] ?? '', $user['token']);
            $db->query('UPDATE users SET last_active = ? WHERE id = ?', $now, $user['id']);
        }
    });

    $router->post('json:api/auth', function($body) use ($user_mod, $login) {
        $user_id = $user_mod->authenticate((string) ($body['email'] ?? ''), (string) ($body['password'] ?? ''));

        return json_encode($user_id === false
            ? [ 'success' => false, 'error' => 'invalid_credentials' ]
            : $login($user_id));
    });

    $router->post('json:api/logout', function() use ($db, $getAuthToken, $setAuthToken) {
        $token = $getAuthToken();

        if ($token) {
            $db->delete('tokens', $token, 'token');
        }

        return json_encode([ 'success' => $setAuthToken('', time() - 3600) ]);
    });

    $router->get('json:api/me', function() use (&$user) {
        $me = $user;

        if ($me) {
            unset($me['password'], $me['token']);
            foreach (\Aurora\App\Permission::getPermissions() as $action) {
                $me['actions'][$action] = \Aurora\App\Permission::can($action);
            }
        }

        return json_encode($me);
    });

    $router->get('json:api/me/sessions', function() use ($db, &$user) {
        return json_encode(array_map(fn($t) => [
            'id' => $t['id'],
            'user_agent' => $t['user_agent'],
            'ip' => $t['ip'],
            'current' => $t['token'] == $user['token'],
            'created_at' => $t['created_at'],
            'updated_at' => $t['updated_at'],
        ], $db->query('SELECT * FROM tokens WHERE user_id = ?', $user['id'])->fetchAll()));
    });

    $router->delete('json:api/me/sessions/{id}', function() use ($db, &$user) {
        $stmt = $db->query('DELETE FROM tokens WHERE id = ? AND user_id = ?', $_GET['id'], $user['id']);
        return json_encode([ 'success' => $stmt->rowCount() > 0 ]);
    });

    $router->get('json:api/settings', function() use ($db, $lang) {
        $settings = \Aurora\App\Setting::get();

        if (!empty($settings['logo'])) {
            $settings['logo'] = Helper::getContentPath($settings['logo']);
        }

        if (!\Aurora\App\Permission::can('edit_settings')) {
            return json_encode(array_intersect_key($settings,
                array_flip([ 'blog_url', 'views_count', 'language', 'timezone', 'date_format' ])));
        }

        $themes_dir = Helper::getPath(Kernel::config('views') . '/themes');

        return json_encode([
            ...$settings,
            'meta' => [
                'roles' => $db->query('SELECT * FROM roles ORDER BY level ASC')->fetchAll(),
                'themes' => array_filter(scandir($themes_dir), fn($file) => is_dir("$themes_dir/$file") && $file != '.' && $file != '..'),
                'languages' => $lang->getAll(),
                'timezones' => \DateTimeZone::listIdentifiers(),
                'db_dsn' => $db->dsn,
            ],
        ]);
    });

    $router->post('json:api/users/impersonate', function($body) use ($user_mod, $login, &$user) {
        $subject = $user_mod->get([
            'id' => $body['id'] ?? 0,
            'status' => 1,
        ]);

        if (!\Aurora\App\Permission::can('impersonate') || empty($subject) || (int) ($subject['role'] ?? 0) >= (int) ($user['role'] ?? 0)) {
            return [ '', 403 ];
        }

        return json_encode($login($subject['id']));
    });

    $router->post('json:api/media/create_folder', function($body) {
        if (!\Aurora\App\Permission::can('edit_media')) {
            return [ '', 403 ];
        }

        try {
            $success = \Aurora\App\Media::addFolder(Kernel::config('content') . '/' . ltrim($body['name'] ?? '', '/'));
        } catch (Exception) {
            $success = false;
        }

        return json_encode([ 'success' => $success ]);
    });

    $router->post('json:api/media/duplicate', function($body) {
        if (empty($body['name']) || str_contains($body['name'], '/')) {
            return json_encode([
                'success' => false,
                'error' => 'invalid_value',
            ]);
        }

        if (!\Aurora\App\Permission::can('edit_media')) {
            return [ '', 403 ];
        }

        try {
            $success = \Aurora\App\Media::duplicate($body['path'] ?? '', $body['name']);
        } catch (Exception) {
            $success = false;
        }

        return json_encode([ 'success' => $success ]);
    });

    $router->post('json:api/media/rename', function($body) {
        if (empty($body['name']) || str_contains($body['name'], '/')) {
            return json_encode([ 'success' => false ]);
        }

        if (!\Aurora\App\Permission::can('edit_media')) {
            return [ '', 403 ];
        }

        try {
            $success = \Aurora\App\Media::rename($body['path'] ?? '', $body['name']);
        } catch (Exception) {
            $success = false;
        }

        return json_encode([ 'success' => $success ]);
    });

    $router->post('json:api/media/move', function($body) {
        if (!\Aurora\App\Permission::can('edit_media')) {
            return [ '', 403 ];
        }

        try {
            $success = \Aurora\App\Media::move($body['path'] ?? '', $body['name']);
        } catch (Exception) {
            $success = false;
        }

        return json_encode([ 'success' => $success ]);
    });

    $router->post('json:api/media/upload', function() {
        if (!\Aurora\App\Permission::can('edit_media')) {
            return [ '', 403 ];
        }

        $success = true;
        $path = Kernel::config('content') . '/' . ltrim($_GET['path'] ?? '', '/');
        $files = [];

        if (isset($_FILES['file']['name']) && !is_array($_FILES['file']['name'])) {
            $files[] = $_FILES['file'];
        } else {
            foreach (array_keys($_FILES['file']['name'] ?? []) as $i) {
                foreach (array_keys($_FILES['file']) as $prop) {
                    $files[$i][$prop] = $_FILES['file'][$prop][$i];
                }
            }
        }

        foreach ($files as $file) {
            if (!\Aurora\App\Media::uploadFile($file, $path)) {
                $success = false;
            }
        }

        return json_encode([ 'success' => $success ]);
    });

    $router->get('api/media/download', function() {
        $file_path = Helper::getPath('content.zip');
        $path = Helper::getPath(Kernel::config('content') . '/' . ltrim($_GET['path'] ?? '', '/'));

        if (!\Aurora\App\Media::isValidPath($path)) {
            return [ '', 403 ];
        }

        $zip = new ZipArchive();
        $zip->open($file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (\Aurora\Core\Helper::getFileIterator($path) as $file) {
            $real_path = $file->getRealPath();
            $relative_path = mb_substr($real_path, mb_strlen($path) + 1);

            if (!$file->isDir()) {
                $zip->addFile($real_path, $relative_path);
            } elseif ($relative_path !== false) {
                $zip->addEmptyDir($relative_path);
            }
        }

        $zip->close();
        Helper::downloadFile($file_path, 'media.zip', 'application/zip');
    });

    $router->get('json:api/media/folders', function() {
        $folders = [ Kernel::config('content') => '/' ];
        $content_dir = Helper::getPath(Kernel::config('content'));

        foreach (new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($content_dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $file) {
            if ($file->isDir()) {
                $folders[] = mb_substr($file->getPathname(), mb_strlen($content_dir) + 1);
            }
        }

        natcasesort($folders);

        return json_encode(array_values($folders));
    });

    $router->any('json:api/media/upload_image', function() {
        if (!\Aurora\App\Permission::can('edit_media')) {
            return [ '', 403 ];
        }

        $file_path = \Aurora\App\Media::uploadFile($_FILES['file'], Kernel::config('content') . '/' . date('Y/m/'));

        if ($file_path === false) {
            return [ '', 500 ];
        }

        return json_encode([ 'location' => '/' . $file_path ]);
    });

    $router->post('json:api/media', function() {
        if (!\Aurora\App\Permission::can('edit_media')) {
            return [ '', 403 ];
        }

        $success = true;
        $path = Kernel::config('content') . '/' . ltrim($_GET['path'] ?? '', '/');
        $files = [];

        if (isset($_FILES['file']['name']) && !is_array($_FILES['file']['name'])) {
            $files[] = $_FILES['file'];
        } else {
            foreach (array_keys($_FILES['file']['name'] ?? []) as $i) {
                foreach (array_keys($_FILES['file']) as $prop) {
                    $files[$i][$prop] = $_FILES['file'][$prop][$i];
                }
            }
        }

        foreach ($files as $file) {
            if (!\Aurora\App\Media::uploadFile($file, $path)) {
                $success = false;
            }
        }

        return json_encode([ 'success' => $success ]);
    });

    $router->get('json:api/db', function() use ($db) {
        if (!\Aurora\App\Permission::can('edit_settings')) {
            return [ '', 403 ];
        }

        return json_encode([
            'meta' => [
                'created' => date('Y-m-d H:i:s'),
                'version' => Kernel::VERSION,
            ],
            'tables' => (new \Aurora\App\Migration($db))->export(),
        ]);
    });

    $router->post('json:api/db', function() use ($db, $lang) {
        if (!\Aurora\App\Permission::can('edit_settings')) {
            return [ '', 403 ];
        }

        $error = false;

        try {
            $json = json_decode(file_get_contents($_FILES['file']['tmp_name'] ?? ''), true);
            $version = is_scalar($json['meta']['version'] ?? null) ? explode('.', (string) $json['meta']['version']) : null;

            if (!isset($version) || explode('.', \Aurora\Core\Kernel::VERSION)[0] != $version[0]) {
                $error = 'invalid_db_version';
            } elseif (!(new \Aurora\App\Migration($db))->import($json['tables'] ?? false)) {
                $error = 'invalid_db_file';
            }
        } catch (\Throwable) {
            $error = 'invalid_db_file';
        }

        $data = [
            'success' => $error === false,
        ];

        if ($error !== false) {
            $data['error'] = $error;
        }

        return json_encode($data);
    });

    $router->get('api/logs', function() {
        if (!\Aurora\App\Permission::can('edit_settings')) {
            return [ '', 403 ];
        }

        $path = \Aurora\Core\Helper::getPath(\Aurora\App\Setting::get('log_file'));
        return file_exists($path) ? file_get_contents($path) : '';
    });

    $router->delete('json:api/logs', function() {
        if (!\Aurora\App\Permission::can('edit_settings')) {
            return [ '', 403 ];
        }

        $path = Helper::getPath(\Aurora\App\Setting::get('log_file'));

        return json_encode([ 'success' => !file_exists($path) || (is_writable($path) && unlink($path)) ]);
    });

    $router->post('json:api/reset_views_count', function() use ($db) {
        if (!\Aurora\App\Permission::can('edit_settings')) {
            return [ '', 403 ];
        }

        return json_encode([ 'success' => $db->delete('views') ]);
    });

    $router->post('json:api/settings', function($body) use ($db) {
        if (!\Aurora\App\Permission::can('edit_settings')) {
            return [ '', 403 ];
        }

        try {
            $db->connection->beginTransaction();

            foreach ($body as $key => $val) {
                if ($key === 'logo') {
                    $val = Helper::normalizeContentPath($val);
                }

                $db->replace('settings', [ 'key' => $key, 'value' => $val ]);
            }

            $success = $db->connection->commit();
        } catch (\PDOException $e) {
            $db->connection->rollBack();
            error_log($e->getMessage());
            $success = false;
        }

        return json_encode([ 'success' => $success ]);
    });

    $router->get('json:api/server', function() use ($db) {
        if (!\Aurora\App\Permission::can('edit_settings')) {
            return [ '', 403 ];
        }

        return json_encode([
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'php_version' => phpversion(),
            'db_dsn' => $db->dsn,
            'root_folder' => rtrim(\Aurora\Core\Helper::getPath(), '/'),
            'date' => date('Y-m-d H:i:s'),
            'memory_limit' => \Aurora\Core\Helper::getPhpSize(ini_get('memory_limit')),
            'file_size_limit' => \Aurora\App\Media::getMaxUploadFileSize(),
        ]);
    });

    $router->get('json:api/update_version', function() {
        if (!\Aurora\App\Permission::can('update')) {
            return [ '', 403 ];
        }

        return json_encode((new \Aurora\App\Update())->getLatestRelease());
    });

    $router->post('json:api/update', function($body) {
        if (!\Aurora\App\Permission::can('update')) {
            return [ '', 403 ];
        }

        $result = (new \Aurora\App\Update())->run($body['zip'] ?? '', fn($line) => Helper::log($line));
        $error = match ($result) {
            \Aurora\App\Update::ERROR_CONNECTION => 'update_error_connection',
            \Aurora\App\Update::ERROR_ZIP => 'update_error_zip',
            \Aurora\App\Update::ERROR_COPY => 'update_error_copy',
            \Aurora\App\Update::ERROR_BUILD => 'update_error_build',
            \Aurora\App\Update::ERROR_COMPOSER => 'update_error_composer',
            default => null,
        };

        return json_encode([
            'success' => $result === true,
            'error' => $error,
        ]);
    });

    $router->get('json:api/stats', function() use ($db, $post_mod) {
        return json_encode([
            'total_posts' => $db->count('posts', '', $post_mod->getCondition([ 'status' => 1 ])),
            'total_scheduled_posts' => $db->count('posts', '', $post_mod->getCondition([ 'status' => 'scheduled' ])),
            'total_draft_posts' => $db->count('posts', '', 'status != 1'),
            'total_pages' => $db->count('pages', '', 'status = 1'),
            'total_draft_pages' => $db->count('pages', '', 'status != 1'),
            'total_users' => $db->count('users', '', 'status = 1'),
            'total_inactive_users' => $db->count('users', '', 'status != 1'),
        ]);
    });

    $router->get('json:api/view_files', function() use ($theme_dir) {
        $absolute_theme_dir = Helper::getPath(Kernel::config('views') . "/$theme_dir");
        $view_files = [];

        foreach (Helper::getFileIterator($absolute_theme_dir) as $file) {
            if ($file->isFile()) {
                $view_files[] = mb_substr($file->getPathname(), mb_strlen($absolute_theme_dir) + 1);
            }
        }

        natcasesort($view_files);
        return json_encode(array_values($view_files));
    });

    $router->post('json:api/{mod}', function($body) use ($page_mod, $post_mod, $user_mod, $tag_mod, $link_mod, &$user) {
        switch ($_GET['mod']) {
            case 'pages': $mod = $page_mod; break;
            case 'posts': $mod = $post_mod; break;
            case 'users': $mod = $user_mod; break;
            case 'tags': $mod = $tag_mod; break;
            case 'links': $mod = $link_mod; break;
            default:
                return [ '', 404 ];
        }

        $id = $_GET['id'] ?? '';
        $errors = $mod->checkFields($body, $id, $user);

        if (!empty($errors)) {
            return [
                json_encode([
                    'success' => false,
                    'errors' => $errors,
                ]),
                array_intersect([ 'no_permission', 'no_publish_permission' ], $errors) ? 403 : 200,
            ];
        }

        return json_encode([
            'success' => Helper::isValidId($id)
                ? $mod->save($id, $body, $user)
                : ($id = $mod->add($body)) !== false,
            'id' => $id,
        ]);
    });

    $router->delete('json:api/{mod}', function($body) use ($page_mod, $post_mod, $user_mod, $tag_mod, $link_mod, &$user) {
        $ids = isset($body['id'])
            ? array_map(fn($id) => (int) $id, is_array($body['id']) ? $body['id'] : explode(',', $body['id']))
            : null;
        $mod_str = $_GET['mod'] ?? '';

        if (!\Aurora\App\Permission::can("edit_$mod_str")) {
            return [ '', 403 ];
        }

        if ($mod_str !== 'media' && empty($ids)) {
            return [ '', 400 ];
        }

        $success = match ($mod_str) {
            'pages' => $page_mod->remove($ids),
            'posts' => $post_mod->remove($ids),
            'tags' => $tag_mod->remove($ids),
            'links' => $link_mod->remove($ids),
            'users' => (function() use ($user_mod, $ids, &$user) {
                $valid_ids = [];

                foreach ($user_mod->getPage(null, null, 'users.id IN (' . implode(',', $ids) . ')') as $row) {
                    if (\Aurora\App\Permission::editUser($row) && $row['id'] != $user['id']) {
                        $valid_ids[] = $row['id'];
                    }
                }

                $ids = $valid_ids;
                return $user_mod->remove($ids);
            })(),
            'media' => (function() use ($body) {
                $done = 0;

                try {
                    foreach ($body as $path) {
                        $done += \Aurora\App\Media::remove($path);
                    }

                    $success = $done == count($body);
                } catch (Exception) {
                    $success = false;
                }

                return $success;
            })(),
            default => null,
        };

        if ($success === null) {
            return [ '', 404 ];
        }

        return json_encode([ 'success' => $success ]);
    });

    $router->get('json:api/roles', function() use ($db) {
        $roles = [];
        $permissions_data = $db->query('SELECT role_level, permission FROM roles_permissions ORDER BY role_level ASC, permission ASC')->fetchAll();
        foreach ($db->query('SELECT * FROM roles ORDER BY level ASC')->fetchAll() as $role) {
            $role_permissions = [];

            foreach ($permissions_data as $permission) {
                if ($role['level'] >= $permission['role_level']) {
                    $role_permissions[] = $permission['permission'];
                }
            }

            sort($role_permissions);

            $roles[] = [
                'level' => (int) $role['level'],
                'slug' => $role['slug'],
                'permissions' => $role_permissions
            ];
        }

        return json_encode($roles);
    });

    $router->get('json:api/{mod}', function() use ($kernel, $page_mod, $post_mod, $user_mod, $tag_mod, $link_mod) {
        switch ($_GET['mod'] ?? '') {
            case 'pages': $mod = $page_mod; break;
            case 'posts': $mod = $post_mod; break;
            case 'users': $mod = $user_mod; break;
            case 'tags': $mod = $tag_mod; break;
            case 'links': $mod = $link_mod; break;
            case 'media':
                $files = \Aurora\App\Media::getFiles(Kernel::config('content') . '/' . ltrim($_GET['path'] ?? '', '/'),
                    $_GET['search'] ?? '',
                    $_GET['order'] ?? 'type',
                    ($_GET['sort'] ?? 'asc') == 'asc');

                if ($_GET['images'] ?? false) {
                    $files = array_filter($files, fn($file) => !$file['is_file'] || $file['is_image']);
                }

                return json_encode([
                    'data' => $files,
                    'meta' => [
                        'current_page' => 1,
                        'per_page' => false,
                        'prev_page' => false,
                        'next_page' => false,
                        'total_items' => count($files),
                    ],
                ]);
            default:
                return [ '', 404 ];
        }

        $page = (int) max($_GET['page'] ?? 1, 1);
        $per_page = $kernel->config('per_page');
        $where = $mod->getCondition($_GET);

        return json_encode([
            'data' => $mod->getPage($page, $per_page, $where, $_GET['order'] ?? $mod::DEFAULT_ORDER, ($_GET['sort'] ?? ($mod::DEFAULT_SORT ?? 'asc')) == 'asc'),
            'meta' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'prev_page' => $page > 1,
                'next_page' => $mod->isNextPageAvailable($page, $per_page, $where),
                'total_items' => $mod->count($where),
            ],
        ]);
    });
};
