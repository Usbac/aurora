<!DOCTYPE html>
<html lang="<?= e(\Aurora\System\Container::get('language')->getCode()) ?>">
<head>
    <title>
        <?php $this->sectionStart('title') ?>
        <?php $this->sectionEnd() ?>
    </title>
    <?= $this->include('admin/partials/head.php') ?>
</head>
<body class="admin">
    <nav>
        <header>
            <img src="/public/assets/logo.svg"/>
            <h1>Aurora</h1>
        </header>
        <div class="admin-options">
            <a href="/admin/dashboard">
                <?= $this->include('icons/home.svg') ?> <?= t('dashboard') ?>
            </a>
            <a href="<?= e($this->url()) ?>" target="_blank">
                <?= $this->include('icons/window.svg') ?> <?= t('view_site') ?>
            </a>
            <a href="/admin/pages" separator>
                <?= $this->include('icons/book.svg') ?> <?= t('pages') ?>
            </a>
            <a href="/admin/posts">
                <?= $this->include('icons/pencil.svg') ?> <?= t('posts') ?>
            </a>
            <a href="/admin/tags">
                <?= $this->include('icons/tag.svg') ?> <?= t('tags') ?>
            </a>
            <a href="/admin/media">
                <?= $this->include('icons/image.svg') ?> <?= t('media') ?>
            </a>
            <a href="/admin/users">
                <?= $this->include('icons/user.svg') ?> <?= t('users') ?>
            </a>
            <a href="/admin/links">
                <?= $this->include('icons/link.svg') ?> <?= t('links') ?>
            </a>
            <a href="/admin/settings">
                <?= $this->include('icons/settings.svg') ?> <?= t('settings') ?>
            </a>
        </div>
        <div class="current-user">
            <a href="/admin/users/edit?id=<?= e($_SESSION['user']['id']) ?>" title="<?= e($_SESSION['user']['name']) ?>">
                <?php if (!empty($_SESSION['user']['image'])): ?>
                    <img src="<?= e($this->getContentUrl($_SESSION['user']['image'])) ?>"/>
                <?php else: ?>
                    <img src="/public/assets/no-image.svg" class="empty-img"/>
                <?php endif ?>
            </a>
            <div id="toggle-theme" class="pointer" title="<?= t('switch_theme') ?>" data-theme="<?php if (($_COOKIE['theme'] ?? '') !== 'dark'): ?>light<?php else: ?>dark<?php endif ?>">
                <?= $this->include('icons/moon.svg') ?>
                <?= $this->include('icons/sun.svg') ?>
            </div>
            <a href="/admin/logout" class="pointer" title="<?= t('logout') ?>">
                <?= $this->include('icons/logout.svg') ?>
            </a>
        </div>
    </nav>
    <noscript class="warning">Looks like JavaScript is disabled or your browser does not support it. JavaScript is required for the site to work properly.</noscript>
    <div class="nav-background" onclick="document.body.toggleAttribute('nav-open')"></div>
    <?= $this->include('admin/partials/snackbar.php') ?>
    <?php $this->sectionStart('content') ?>
    <?php $this->sectionEnd() ?>
</body>
</html>
<script>
    window.addEventListener('load', () => {
        updateMetaThemeColor(<?= js(($_COOKIE['theme'] ?? '') !== 'dark') ?>);

        document.querySelectorAll('.admin-options > a').forEach(el => {
            if (location.pathname.startsWith(el.getAttribute('href'))) {
                el.dataset.checked = true;
            }
        });

        Dropdown.init();
    });

    function updateMetaThemeColor(light) {
        document.querySelector('meta[name="theme-color"]').setAttribute('content', light ? '#ffffff' : '#171821');
    }

    document.getElementById('toggle-theme').addEventListener('click', () => {
        let is_light_enabled = get('#css-dark').toggleAttribute('disabled');
        let theme = is_light_enabled ? 'light' : 'dark';

        updateMetaThemeColor(is_light_enabled);
        get('#toggle-theme').dataset.theme = theme;
        document.cookie = 'theme=' + theme + ';path=/';
    });
</script>
<?php $this->sectionStart('extra') ?>
<?php $this->sectionEnd() ?>
