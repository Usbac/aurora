<?php

use Aurora\Core\{DB, Helper, Kernel, Language, Route, View};

const ITEMS_PER_PAGE = 20;

return function (Route $router, DB $db, View $view, Language $lang) {
    $user_mod = new \Aurora\App\Modules\User($db, $lang);
    $tag_mod = new \Aurora\App\Modules\Tag($db, $lang);
    $link_mod = new \Aurora\App\Modules\Link($db, $lang);
    $page_mod = new \Aurora\App\Modules\Page($db, $lang);
    $post_mod = new \Aurora\App\Modules\Post($db, $lang);

    $blog_url = \Aurora\App\Setting::get('blog_url');
    $theme_dir = 'themes/' . \Aurora\App\Setting::get('theme');
    $rss = \Aurora\App\Setting::get('rss');

    $router->middleware('*', function() use ($db, $view, $lang, $theme_dir) {
        if (Helper::isValidId($_SESSION['user']['id'] ?? false)) {
            $_SESSION['user'] = $db->query('SELECT * FROM users WHERE id = ? AND status', $_SESSION['user']['id'])->fetch();
        }

        if (\Aurora\App\Setting::get('maintenance') && !str_starts_with(Helper::getCurrentPath(), 'admin') && !Helper::isValidId($_SESSION['user']['id'] ?? false)) {
            echo $view->get("$theme_dir/information.php", [
                'description' => $lang->get('under_maintenance'),
                'subdescription' => $lang->get('come_back_soon'),
            ]);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Helper::isCsrfTokenValid($_POST['csrf'] ?? '')) {
            echo json_encode([ 'reload' => true ]);
            exit;
        }
    });

    $router->code(404, function() use ($view, $lang, $theme_dir) {
        return $view->get("$theme_dir/information.php", [
            'title' => '404',
            'description' => $lang->get('not_found'),
            'subdescription' => $lang->get('not_found_desc'),
        ]);
    });

    /**
     * ADMIN
     */

    $router->middleware('admin/*', function() use ($db) {
        if ((!Helper::isValidId($_SESSION['user']['id'] ?? false) || !($_SESSION['user']['status'] ?? false)) &&
            !in_array(Helper::getCurrentPath(), [ 'admin', 'admin/login', 'admin/send_password_restore', 'admin/new_password', 'admin/password_restore' ])) {
            header('Location: /admin');
            exit;
        }

        if (Helper::isValidId($_SESSION['user']['id'] ?? false)) {
            $db->update('users', [ 'last_active' => time() ], $_SESSION['user']['id']);
        }
    });

    $router->get('admin', function() use ($view) {
        if (Helper::isValidId($_SESSION['user']['id'] ?? false)) {
            header('Location: /admin/dashboard');
        }

        return $view->get('admin/login.php');
    });

    $router->post('json:admin/login', function() use ($user_mod, $lang) {
        $errors = $user_mod->handleLogin($_POST['email'], $_POST['password']);

        return json_encode([
            'success' => empty($errors),
            'msg' => null,
            'errors' => $errors,
        ]);
    });

    $router->post('json:admin/send_password_restore', function() use ($view, $user_mod) {
        $hash = bin2hex(random_bytes(18));
        $errors = $user_mod->requestPasswordRestore($_POST['email'],
            $hash,
            $view->get('admin/emails/password_restore.php', [ 'hash' => $hash ]));

        return json_encode([
            'success' => empty($errors),
            'errors' => $errors,
        ]);
    });

    $router->get('admin/new_password', function() use ($view) {
        return $view->get('admin/password_restore.php', [ 'hash' => $_GET['hash'] ]);
    });

    $router->post('json:admin/password_restore', function() use ($user_mod) {
        $error = $user_mod->passwordRestore($_POST['hash'], $_POST['password'], $_POST['password_confirm']);
        return json_encode([
            'success' => empty($error),
            'errors' => [ $error ],
        ]);
    });

    $router->get('admin/logout', function() {
        session_destroy();
        header('Location: /admin');
    });

    $router->get('admin/dashboard', function() use ($db, $view, $link_mod, $post_mod) {
        return $view->get('admin/dashboard.php', [
            'links' => $link_mod->getPage(null, null, '', 'order'),
            'posts' => $post_mod->getPage(1, 6, $post_mod->getCondition([ 'status' => 1 ]), 'published_at', false),
            'total_posts' => $db->count('posts', '', $post_mod->getCondition([ 'status' => 1 ])),
            'total_scheduled_posts' => $db->count('posts', '', $post_mod->getCondition([ 'status' => 'scheduled' ])),
            'total_draft_posts' => $db->count('posts', '', 'status != 1'),
            'total_pages' => $db->count('pages', '', 'status = 1'),
            'total_draft_pages' => $db->count('pages', '', 'status != 1'),
            'total_users' => $db->count('users', '', 'status = 1'),
            'total_inactive_users' => $db->count('users', '', 'status != 1'),
        ]);
    });

    /* PAGES */

    $router->get('admin/pages', function() use ($view, $lang, $page_mod) {
        return $view->get('admin/list.php', [
            'title' => $lang->get('pages'),
            'show_add_button' => \Aurora\App\Permission::can('edit_pages'),
            'columns' => [
                [ 'title' => '', 'class' => 'w100' ],
                [ 'title' => $lang->get('slug'), 'class' => 'w20' ],
                [ 'title' => $lang->get('edited'), 'class' => 'w20' ],
                [ 'title' => $lang->get('number_views'), 'class' => 'w10 numeric', 'condition' => \Aurora\App\Setting::get('views_count') ],
                [ 'title' => '', 'class' => 'w10 row-actions' ],
            ],
            'extra_header' => 'admin/partials/extra_headers/pages.php',
            'filters' => [
                'status' => [
                    'title' => $lang->get('status'),
                    'options' => [
                        '' => $lang->get('all'),
                        '1' => $lang->get('published'),
                        '0' => $lang->get('draft'),
                    ],
                ],
                'order' => [
                    'title' => $lang->get('sort_by'),
                    'options' => [
                        'title' => $lang->get('title'),
                        'status' => $lang->get('status'),
                        'slug' => $lang->get('slug'),
                        'edited' => $lang->get('edited'),
                        'views' => $lang->get('number_views'),
                    ],
                ],
                'sort' => [
                    'options' => [
                        'asc' => $lang->get('ascending'),
                        'desc' => $lang->get('descending'),
                    ],
                ],
            ],
            'defaults' => [
                'order' => $page_mod::DEFAULT_ORDER,
                'sort' => $page_mod::DEFAULT_SORT,
            ],
        ]);
    });

    $router->get('admin/pages/edit', function() use ($view, $link_mod, $page_mod, $theme_dir) {
        $page = Helper::isValidId($_GET['id'] ?? false) ? $page_mod->get([ 'id' => $_GET['id'] ]) : [];
        if (!$page && isset($_GET['id'])) {
            http_response_code(404);
            return;
        }

        $absolute_theme_dir = Helper::getPath(Kernel::config('views') . "/$theme_dir");
        $view_files = [];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolute_theme_dir)) as $file) {
            if ($file->isFile()) {
                $view_files[] = mb_substr($file->getPathname(), mb_strlen($absolute_theme_dir) + 1);
            }
        }

        natcasesort($view_files);

        return $view->get('admin/page.php', [
            'header_links' => $link_mod->getHeaderLinks(),
            'page' => $page,
            'view_files' => $view_files,
        ]);
    });

    $router->post('json:admin/pages/remove', function() use ($lang, $page_mod) {
        if (!\Aurora\App\Permission::can('edit_pages')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        if (!$page_mod->remove(explode(',', $_POST['id']))) {
            http_response_code(500);
            return json_encode([ 'errors' => [ $lang->get('unexpected_error') ] ]);
        }

        return json_encode([ 'success' => true ]);
    });

    /* POSTS */

    $router->get('admin/posts', function() use ($view, $lang, $post_mod, $user_mod) {
        $authors = [ '' => $lang->get('all') ];
        foreach ($user_mod->getPage() as $user) {
            $authors[$user['id']] = $user['name'];
        }

        return $view->get('admin/list.php', [
            'title' => $lang->get('posts'),
            'show_add_button' => \Aurora\App\Permission::can('edit_posts'),
            'columns' => [
                [ 'title' => '', 'class' => 'w100' ],
                [ 'title' => $lang->get('author'), 'class' => 'w20' ],
                [ 'title' => $lang->get('publish_date'), 'class' => 'w20' ],
                [ 'title' => $lang->get('number_views'), 'class' => 'w10 numeric', 'condition' => \Aurora\App\Setting::get('views_count') ],
                [ 'title' => '', 'class' => 'w10 row-actions' ],
            ],
            'extra_header' => 'admin/partials/extra_headers/posts.php',
            'filters' => [
                'user' => [
                    'title' => $lang->get('author'),
                    'options' => $authors,
                ],
                'status' => [
                    'title' => $lang->get('status'),
                    'options' => [
                        '' => $lang->get('all'),
                        '1' => $lang->get('published'),
                        'scheduled' => $lang->get('scheduled'),
                        '0' => $lang->get('draft'),
                    ],
                ],
                'order' => [
                    'title' => $lang->get('sort_by'),
                    'options' => [
                        'title' => $lang->get('title'),
                        'author' => $lang->get('author'),
                        'date' => $lang->get('publish_date'),
                        'views' => $lang->get('number_views'),
                    ],
                ],
                'sort' => [
                    'options' => [
                        'asc' => $lang->get('ascending'),
                        'desc' => $lang->get('descending'),
                    ],
                ],
            ],
            'defaults' => [
                'order' => $post_mod::DEFAULT_ORDER,
                'sort' => $post_mod::DEFAULT_SORT,
            ],
        ]);
    });

    $router->get('admin/posts/edit', function() use ($view, $user_mod, $tag_mod, $post_mod) {
        $post = Helper::isValidId($_GET['id'] ?? false) ? $post_mod->get([ 'id' => $_GET['id'] ]) : [];
        if (!$post && isset($_GET['id'])) {
            http_response_code(404);
            return;
        }

        return $view->get('admin/post.php', [
            'users' => $user_mod->getPage(),
            'tags' => $tag_mod->getPage(null, null, '', 'name'),
            'post' => $post,
        ]);
    });

    $router->post('json:admin/posts/remove', function() use ($lang, $post_mod) {
        if (!\Aurora\App\Permission::can('edit_posts')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        if (!$post_mod->remove(explode(',', $_POST['id']))) {
            http_response_code(500);
            return json_encode([ 'errors' => [ $lang->get('unexpected_error') ] ]);
        }

        return json_encode([ 'success' => true ]);
    });

    $router->any('json:admin/posts/upload_image', function() {
        $path = Kernel::config('content') . '/' . date('Y/m/');
        \Aurora\App\Media::uploadFile($_FILES['file'], $path);

        return json_encode([ 'location' => "/$path/" . $_FILES['file']['name'] ]);
    });

    /* USERS */

    $router->get('admin/users', function() use ($db, $view, $lang, $user_mod) {
        $roles = [ '' => $lang->get('all') ];
        foreach ($db->query('SELECT * FROM roles ORDER BY level ASC')->fetchAll() as $role) {
            $roles[$role['level']] = $lang->get($role['slug']);
        }

        return $view->get('admin/list.php', [
            'title' => $lang->get('users'),
            'show_add_button' => \Aurora\App\Permission::can('edit_users'),
            'columns' => [
                [ 'title' => '', 'class' => 'w100' ],
                [ 'title' => $lang->get('role'), 'class' => 'w20' ],
                [ 'title' => $lang->get('last_active'), 'class' => 'w20' ],
                [ 'title' => $lang->get('number_posts'), 'class' => 'w10 numeric' ],
                [ 'title' => '', 'class' => 'w10 row-actions' ],
            ],
            'extra_header' => 'admin/partials/extra_headers/users.php',
            'filters' => [
                'status' => [
                    'title' => $lang->get('status'),
                    'options' => [
                        '' => $lang->get('all'),
                        '1' => $lang->get('active'),
                        '0' => $lang->get('inactive'),
                    ],
                ],
                'role' => [
                    'title' => $lang->get('role'),
                    'options' => $roles,
                ],
                'order' => [
                    'title' => $lang->get('sort_by'),
                    'options' => [
                        'name' => $lang->get('name'),
                        'email' => $lang->get('email'),
                        'status' => $lang->get('status'),
                        'role' => $lang->get('role'),
                        'last_active' => $lang->get('last_active'),
                        'posts' => $lang->get('number_posts'),
                    ],
                ],
                'sort' => [
                    'options' => [
                        'asc' => $lang->get('ascending'),
                        'desc' => $lang->get('descending'),
                    ],
                ],
            ],
            'defaults' => [
                'order' => $user_mod::DEFAULT_ORDER,
                'sort' => $user_mod::DEFAULT_SORT,
            ],
        ]);
    });

    $router->get('admin/users/edit', function() use ($db, $view, $user_mod) {
        $user = Helper::isValidId($_GET['id'] ?? false) ? $user_mod->get([ 'id' => $_GET['id'] ]) : [];
        if (!$user && isset($_GET['id'])) {
            http_response_code(404);
            return;
        }

        return $view->get('admin/user.php', [
            'user' => $user,
            'roles' => $db->query('SELECT * FROM roles ORDER BY level ASC')->fetchAll(),
        ]);
    });

    $router->post('json:admin/users/remove', function() use ($lang, $user_mod) {
        if (!\Aurora\App\Permission::can('edit_users')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        if (!$user_mod->remove(array_filter(explode(',', $_POST['id']), fn($id) => $id != $_SESSION['user']['id']))) {
            http_response_code(500);
            return json_encode([ 'errors' => [ $lang->get('unexpected_error') ] ]);
        }

        return json_encode([ 'success' => true ]);
    });

    $router->get('admin/users/impersonate', function() use ($user_mod) {
        $user = $user_mod->get([
            'id' => $_GET['id'] ?? 0,
            'status' => 1,
        ]);

        if (!\Aurora\App\Permission::impersonate($user)) {
            http_response_code(403);
            return;
        }

        $_SESSION['user'] = $user;
        header('Location: /admin/users');
    });

    /* LINKS */

    $router->get('admin/links', function() use ($view, $lang, $link_mod) {
        return $view->get('admin/list.php', [
            'title' => $lang->get('links'),
            'show_add_button' => \Aurora\App\Permission::can('edit_links'),
            'columns' => [
                [ 'title' => '', 'class' => 'w100' ],
                [ 'title' => $lang->get('url'), 'class' => 'w20' ],
                [ 'title' => $lang->get('status'), 'class' => 'w20' ],
                [ 'title' => $lang->get('order'), 'class' => 'w10 numeric' ],
                [ 'title' => '', 'class' => 'w10 row-actions' ],
            ],
            'extra_header' => 'admin/partials/extra_headers/links.php',
            'filters' => [
                'status' => [
                    'title' => $lang->get('status'),
                    'options' => [
                        '' => $lang->get('all'),
                        '1' => $lang->get('active'),
                        '0' => $lang->get('inactive'),
                    ],
                ],
                'order' => [
                    'title' => $lang->get('sort_by'),
                    'options' => [
                        'title' => $lang->get('title'),
                        'url' => $lang->get('url'),
                        'status' => $lang->get('status'),
                        'order' => $lang->get('order'),
                    ],
                ],
                'sort' => [
                    'options' => [
                        'asc' => $lang->get('ascending'),
                        'desc' => $lang->get('descending'),
                    ],
                ],
            ],
            'defaults' => [
                'order' => $link_mod::DEFAULT_ORDER,
                'sort' => $link_mod::DEFAULT_SORT,
            ],
        ]);
    });

    $router->get('admin/links/edit', function() use ($view, $link_mod) {
        $link = Helper::isValidId($_GET['id'] ?? false) ? $link_mod->get([ 'id' => $_GET['id'] ]) : [];
        if (!$link && isset($_GET['id'])) {
            http_response_code(404);
            return;
        }

        return $view->get('admin/link.php', [
            'link' => $link,
        ]);
    });

    $router->post('json:admin/links/remove', function() use ($lang, $link_mod) {
        if (!\Aurora\App\Permission::can('edit_links')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        if (!$link_mod->remove(explode(',', $_POST['id']))) {
            http_response_code(500);
            return json_encode([ 'errors' => [ $lang->get('unexpected_error') ] ]);
        }

        return json_encode([ 'success' => true ]);
    });

    /* TAGS */

    $router->get('admin/tags', function() use ($view, $lang, $tag_mod) {
        return $view->get('admin/list.php', [
            'title' => $lang->get('tags'),
            'show_add_button' => \Aurora\App\Permission::can('edit_tags'),
            'columns' => [
                [ 'title' => '', 'class' => 'w100' ],
                [ 'title' => $lang->get('slug'), 'class' => 'w30' ],
                [ 'title' => $lang->get('number_posts'), 'class' => 'w10 numeric' ],
                [ 'title' => '', 'class' => 'w10 row-actions' ],
            ],
            'extra_header' => 'admin/partials/extra_headers/tags.php',
            'filters' => [
                'order' => [
                    'title' => $lang->get('sort_by'),
                    'options' => [
                        'name' => $lang->get('name'),
                        'slug' => $lang->get('slug'),
                        'posts' => $lang->get('number_posts'),
                    ],
                ],
                'sort' => [
                    'options' => [
                        'asc' => $lang->get('ascending'),
                        'desc' => $lang->get('descending'),
                    ],
                ],
            ],
            'defaults' => [
                'order' => $tag_mod::DEFAULT_ORDER,
                'sort' => $tag_mod::DEFAULT_SORT,
            ],
        ]);
    });

    $router->get('admin/tags/edit', function() use ($view, $tag_mod) {
        $tag = Helper::isValidId($_GET['id'] ?? false) ? $tag_mod->get([ 'id' => $_GET['id'] ]) : [];
        if (!$tag && isset($_GET['id'])) {
            http_response_code(404);
            return;
        }

        return $view->get('admin/tag.php', [
            'tag' => $tag,
        ]);
    });

    $router->post('json:admin/tags/remove', function() use ($lang, $tag_mod) {
        if (!\Aurora\App\Permission::can('edit_tags')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        if (!$tag_mod->remove(explode(',', $_POST['id']))) {
            http_response_code(500);
            return json_encode([ 'errors' => [ $lang->get('unexpected_error') ] ]);
        }

        return json_encode([ 'success' => true ]);
    });

    /* MEDIA */

    $router->get('admin/media', function() use ($view, $lang) {
        $folders = [ Kernel::config('content') => '/' ];
        $root_dir = Helper::getPath();
        $content_dir = Helper::getPath(Kernel::config('content'));
        $path = $_GET['path'] ?? Kernel::config('content');
        $absolute_path = Helper::getPath($path);

        if ($path == Kernel::config('content') && !file_exists($absolute_path)) {
            mkdir($absolute_path, \Aurora\App\Media::FOLDER_PERMISSION);
        }

        if (!\Aurora\App\Media::isValidPath($absolute_path) || !file_exists($absolute_path)) {
            http_response_code(404);
            return;
        }

        foreach (new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($content_dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $file) {
            if ($file->isDir()) {
                $folder_dir = $file->getPathname();
                $folders[mb_substr($folder_dir, mb_strlen($root_dir) + 1)] = mb_substr($folder_dir, mb_strlen($content_dir) + 1);
            }
        }

        natcasesort($folders);

        return $view->get('admin/list.php', [
            'title' => $lang->get('media'),
            'custom_header' => $view->get('admin/partials/media_header.php', [
                'path' => $path,
                'folders' => $folders,
            ]),
            'columns' => [
                [ 'title' => '', 'class' => 'w100' ],
                [ 'title' => $lang->get('information'), 'class' => 'w20 file-info' ],
                [ 'title' => $lang->get('last_modification'), 'class' => 'w20' ],
                [ 'title' => '', 'class' => 'w10 row-actions' ],
            ],
            'extra_header' => 'admin/partials/extra_headers/media.php',
            'filters' => [
                'order' => [
                    'title' => $lang->get('sort_by'),
                    'options' => [
                        'name' => $lang->get('name'),
                        'type' => $lang->get('type'),
                        'size' => $lang->get('size'),
                    ]
                ],
                'sort' => [
                    'options' => [
                        'asc' => $lang->get('ascending'),
                        'desc' => $lang->get('descending'),
                    ],
                ],
            ],
            'defaults' => [
                'order' => 'type',
                'sort' => 'asc',
            ],
        ]);
    });

    $router->post('json:admin/media/upload', function() use ($lang) {
        if (!\Aurora\App\Permission::can('edit_media')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        $success = true;
        $path = $_GET['path'] ?? Kernel::config('content');
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

        return json_encode([
            'success' => $success,
            'errors' => $success ? [] : [ $lang->get('error_upload_file') ],
        ]);
    });

    $router->post('json:admin/media/create_folder', function() use ($lang) {
        if (!\Aurora\App\Permission::can('edit_media')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        try {
            $success = \Aurora\App\Media::addFolder($_GET['path'] ?? Kernel::config('content'), $_POST['name'] ?? '');
        } catch (Exception) {
            $success = false;
        }

        return json_encode([
            'success' => $success,
            'errors' => $success ? [] : [ $lang->get('error_create_folder') ],
        ]);
    });

    $router->post('json:admin/media/remove', function() use ($lang) {
        if (!\Aurora\App\Permission::can('edit_media')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        $paths = json_decode($_POST['paths'] ?? '') ?? [];
        $done = 0;

        try {
            foreach ($paths as $path) {
                $done += \Aurora\App\Media::remove($path);
            }

            $success = $done == count($paths);
        } catch (Exception) {
            $success = false;
        }

        return json_encode([
            'success' => $success,
            'errors' => $success
                ? []
                : [ $lang->get($done == 0 ? 'error_remove_item' : 'error_remove_some_items') ],
        ]);
    });

    $router->post('json:admin/media/save', function() use ($lang) {
        if (empty($_POST['name']) || str_contains($_POST['name'], '/')) {
            return json_encode([
                'success' => false,
                'errors' => [ 'name' => $lang->get('invalid_value') ]
            ]);
        }

        if (!\Aurora\App\Permission::can('edit_media')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        try {
            $success = \Aurora\App\Media::rename($_POST['path'] ?? '', $_POST['name']);
        } catch (Exception) {
            $success = false;
        }

        return json_encode([
            'success' => $success,
            'errors' => $success ? [] : [ $lang->get('error_rename_item') ],
        ]);
    });

    $router->post('json:admin/media/move', function() use ($lang) {
        if (!\Aurora\App\Permission::can('edit_media')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        $paths = json_decode($_POST['paths'] ?? '') ?? [];
        $done = 0;

        try {
            foreach ($paths as $path) {
                $done += \Aurora\App\Media::move($path, $_POST['name']);
            }

            $success = $done == count($paths);
        } catch (Exception) {
            $success = false;
        }

        return json_encode([
            'success' => $success,
            'errors' => $success
                ? []
                : [ $lang->get($done == 0 ? 'error_move_item' : 'error_move_some_items') ],
        ]);
    });

    $router->post('json:admin/media/duplicate', function() use ($lang) {
        if (empty($_POST['name']) || str_contains($_POST['name'], '/')) {
            return json_encode([
                'success' => false,
                'errors' => [ 'name' => $lang->get('invalid_value') ]
            ]);
        }

        if (!\Aurora\App\Permission::can('edit_media')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        try {
            $success = \Aurora\App\Media::duplicate($_POST['path'] ?? '', $_POST['name']);
        } catch (Exception) {
            $success = false;
        }

        return json_encode([
            'success' => $success,
            'errors' => $success ? [] : [ $lang->get('error_duplicate_item') ],
        ]);
    });

    $router->get('admin/image_dialog', function() use ($view) {
        $path = $_GET['path'] ?? Kernel::config('content');
        $files = \Aurora\App\Media::getFiles($path, '', 'name');
        if ($files === false) {
            http_response_code(404);
            return;
        }

        return $view->get('admin/partials/images_dialog.php', [
            'path' => $path,
            'files' => array_filter($files, fn($file) => !$file['is_file'] || $file['is_image']),
        ]);
    });

    /* SETTINGS */

    $router->get('admin/settings', function() use ($view, $db, $lang) {
        $themes_dir = Helper::getPath(Kernel::config('views') . '/themes');

        return $view->get('admin/settings.php', [
            'roles' => $db->query('SELECT * FROM roles ORDER BY level ASC')->fetchAll(),
            'themes' => array_filter(scandir($themes_dir), fn($file) => is_dir("$themes_dir/$file") && $file != '.' && $file != '..'),
            'languages' => $lang->getAll(),
            'db_dsn' => $db->dsn,
        ]);
    });

    $router->post('json:admin/settings/save', function() use ($db, $lang) {
        if (!\Aurora\App\Permission::can('edit_settings')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        try {
            $db->connection->beginTransaction();

            foreach ($_POST as $key => $val) {
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

    $router->get('json:admin/{mod}/page', function() use ($view, $page_mod, $post_mod, $user_mod, $tag_mod, $link_mod) {
        $mod_str = $_GET['mod'] ?? '';
        switch ($mod_str) {
            case 'pages': $mod = $page_mod; break;
            case 'posts': $mod = $post_mod; break;
            case 'users': $mod = $user_mod; break;
            case 'tags': $mod = $tag_mod; break;
            case 'links': $mod = $link_mod; break;
            case 'media':
                $files = \Aurora\App\Media::getFiles($_GET['path'] ?? Kernel::config('content'), $_GET['search'] ?? '', $_GET['order'] ?? 'type', ($_GET['sort'] ?? 'asc') == 'asc');
                return json_encode([
                    'next_page' => false,
                    'count' => count($files),
                    'html' => $view->get('admin/partials/lists/media.php', [ 'files' => $files ]),
                ]);
            default:
                http_response_code(404);
                return;
        }

        $where = $mod->getCondition($_GET);

        return json_encode([
            'next_page' => $mod->isNextPageAvailable($_GET['page'], ITEMS_PER_PAGE, $where),
            'count' => $mod->count($where),
            'html' => $view->get("admin/partials/lists/$mod_str.php", [
                $mod_str => $mod->getPage($_GET['page'], ITEMS_PER_PAGE, $where, $_GET['order'] ?? $mod::DEFAULT_ORDER, ($_GET['sort'] ?? ($mod::DEFAULT_SORT ?? 'asc')) == 'asc'),
            ]),
        ]);
    });

    $router->post('json:admin/{mod}/save', function() use ($page_mod, $post_mod, $user_mod, $tag_mod, $link_mod) {
        switch ($_GET['mod']) {
            case 'pages': $mod = $page_mod; break;
            case 'posts': $mod = $post_mod; break;
            case 'users': $mod = $user_mod; break;
            case 'tags': $mod = $tag_mod; break;
            case 'links': $mod = $link_mod; break;
            default:
                http_response_code(404);
                return;
        }

        $id = $_GET['id'] ?? '';
        $errors = $mod->checkFields($_POST, $id);
        if (!empty($errors)) {
            return json_encode([
                'success' => false,
                'errors' => $errors,
            ]);
        }

        $success = Helper::isValidId($id)
            ? $mod->save($id, $_POST)
            : ($id = $mod->add($_POST)) !== false;

        return json_encode([
            'success' => $success,
            'id' => $id,
        ]);
    });

    $router->get('json:admin/settings/db', function() use ($db) {
        if (!\Aurora\App\Permission::can('edit_settings')) {
            http_response_code(403);
            return;
        }

        header('Content-disposition: attachment; filename=db.json');

        return json_encode([
            'meta' => [
                'created' => date('Y-m-d H:i:s'),
                'version' => Kernel::VERSION,
            ],
            'tables' => (new \Aurora\App\Migration($db))->export(),
        ]);
    });

    $router->post('json:admin/settings/db_upload', function() use ($db, $lang) {
        if (!\Aurora\App\Permission::can('edit_settings')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        try {
            $json = json_decode(file_get_contents($_FILES['db']['tmp_name'] ?? ''), true);
            $success = (new \Aurora\App\Migration($db))->import($json['tables'] ?? false);
        } catch (\Throwable) {
            $success = false;
        }

        return json_encode([
            'success' => $success,
            'errors' => $success ? '' : $lang->get('invalid_db_file'),
        ]);
    });

    $router->post('json:admin/settings/reset_views_count', function() use ($db, $lang) {
        if (!\Aurora\App\Permission::can('edit_settings')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        return json_encode([ 'success' => $db->delete('views') ]);
    });

    $router->post('json:admin/settings/logs_clear', function() use ($lang) {
        if (!\Aurora\App\Permission::can('edit_settings')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        return json_encode([ 'success' => unlink(Helper::getPath(\Aurora\App\Setting::get('log_file'))) ]);
    });

    $router->get('admin/settings/logs_download', function() {
        Helper::downloadFile(Helper::getPath(\Aurora\App\Setting::get('log_file')), 'Aurora ' . date('Y-m-d H:i:s') . '.log', 'text/plain');
    });

    $router->get('admin/settings/media_download', function() use ($lang) {
        $file_path = Helper::getPath('content.zip');
        $path = $_GET['path'] ?? '';
        $absolute_path = Helper::getPath($path);

        if (!\Aurora\App\Media::isValidPath($absolute_path)) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        $zip = new ZipArchive();
        $zip->open($file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolute_path)) as $file) {
            $real_path = $file->getRealPath();
            $relative_path = mb_substr($real_path, mb_strlen($absolute_path) + 1);

            if (!$file->isDir()) {
                $zip->addFile($real_path, $relative_path);
            } elseif ($relative_path !== false) {
                $zip->addEmptyDir($relative_path);
            }
        }

        $zip->close();
        Helper::downloadFile($file_path, urldecode(trim($path, '/')) . ' ' . date('Y-m-d H:i:s') . '.zip', 'application/zip');
    });

    $router->get('json:admin/settings/update_version', function() {
        return json_encode((new \Aurora\App\Update())->getLatestRelease());
    });

    $router->post('json:admin/settings/update', function() use ($lang) {
        if (!\Aurora\App\Permission::can('update')) {
            http_response_code(403);
            return json_encode([ 'errors' => [ $lang->get('no_permission') ] ]);
        }

        $result = (new \Aurora\App\Update)->run($_POST['zip'] ?? '');
        $error = match ($result) {
            \Aurora\App\Update::ERROR_CONNECTION => $lang->get('update_error_connection'),
            \Aurora\App\Update::ERROR_ZIP => $lang->get('update_error_zip'),
            \Aurora\App\Update::ERROR_COPY => $lang->get('update_error_copy'),
            default => null,
        };

        return json_encode([
            'success' => $result === true,
            'errors' => [ $error ],
        ]);
    });

    /**
     * BLOG
     */

    $router->get('json:api/posts', function() use ($view, $post_mod, $theme_dir) {
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
            'html' => $view->get("$theme_dir/partials/posts_page.php", [
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

        return $view->get("$theme_dir/blog.php", [
            'header_links' => $link_mod->getHeaderLinks(),
            'title' => $lang->get('blog'),
            'posts' => $post_mod->getPage($current_page, $per_page, $where, 'date', false, true),
            'next_page' => $post_mod->isNextPageAvailable($current_page, $per_page, $where),
            'current_page' => $current_page,
        ]);
    });

    $router->get("$blog_url/author/{author}", function() use ($view, $user_mod, $link_mod, $post_mod, $theme_dir) {
        $current_page = max(1, (int) ($_GET['page'] ?? 1));
        $user = $user_mod->get([ 'slug' => $_GET['author'] ]);
        $per_page = \Aurora\App\Setting::get('per_page');

        if (!$user) {
            http_response_code(404);
            return;
        }

        $where = implode(' AND ', [
            $post_mod->getCondition([ 'status' => 1 ]),
            'users.id = ' . ((int) $user['id']),
        ]);

        return $view->get("$theme_dir/blog.php", [
            'header_links' => $link_mod->getHeaderLinks(),
            'title' => $user['name'],
            'user' => $user,
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
            http_response_code(404);
            return;
        }

        $where = implode(' AND ', [
            $post_mod->getCondition([ 'status' => 1 ]),
            'posts.id IN (SELECT post_id FROM posts_to_tags WHERE tag_id = ' . ((int) $tag['id']) . ')',
        ]);

        return $view->get("$theme_dir/blog.php", [
            'header_links' => $link_mod->getHeaderLinks(),
            'title' => $tag['name'],
            'tag' => $tag,
            'posts' => $post_mod->getPage($current_page, $per_page, $where, 'date', false, true),
            'next_page' => $post_mod->isNextPageAvailable($current_page, $per_page, $where),
            'current_page' => $current_page,
        ]);
    });

    $router->get("$blog_url/{slug}", function() use ($db, $view, $link_mod, $post_mod, $theme_dir) {
        $post_cond = $post_mod->getCondition([ 'status' => 1 ]);

        $post = $post_mod->get([
            'slug' => $_GET['slug'] ?? '',
            empty($_SESSION['user']) ? $post_cond : '',
        ]);

        if (!$post) {
            http_response_code(404);
            return;
        }

        if (\Aurora\App\Setting::get('views_count')) {
            $db->replace('views', [
                'type' => 'post',
                'item_id' => $post['id'],
                'ip' => Helper::getUserIP(),
                'date' => time(),
            ]);
        }

        return $view->get("$theme_dir/post.php", [
            'header_links' => $link_mod->getHeaderLinks(),
            'post' => $post,
            'related_posts' => empty($post['tags_id'])
                ? []
                : $post_mod->getPage(1, 3, "$post_cond AND p2t.tag_id IN (" . $post['tags_id'] . ') AND posts.id != ' . $post['id']),
        ]);
    });

    if (!empty($rss)) {
        $router->get("xml:$rss", function() use ($post_mod, $view, $theme_dir) {
            return $view->get("$theme_dir/rss.php", [
                'posts' => $post_mod->getPage(null, null, $post_mod->getCondition([ 'status' => 1 ]), 'date', false),
            ]);
        });
    }

    $router->get([ '/', '{slug}' ], function() use ($db, $view, $link_mod, $page_mod, $theme_dir) {
        $page = $page_mod->get([
            'slug' => $_GET['slug'] ?? '',
            empty($_SESSION['user']) ? $page_mod->getCondition([ 'status' => 1 ]) : '',
        ]);

        if (!$page) {
            http_response_code(404);
            return;
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
            : 'page.php';

        return $view->get("$theme_dir/$template", [
            'header_links' => $link_mod->getHeaderLinks(),
            ...$page,
        ]);
    });
};
