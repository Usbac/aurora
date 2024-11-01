<?php if (setting('maintenance')): ?>
    <div class="maintenance-bar"><?= t('warning_maintenance') ?></div>
<?php endif ?>
<header>
    <div>
        <a href="/" class="logo-img">
            <img class="logo-full" src="<?= e($this->getContentUrl(setting('logo'))) ?>" alt="<?= e(setting('title')) ?>"/>
        </a>
        <?php $current_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'); ?>
        <nav id="menu">
            <ul class="close">
                <li>
                    <button class="pointer" onclick="document.body.toggleAttribute('data-nav-open')">
                        <?= $this->include('icons/x.svg') ?>
                    </button>
                </li>
            </ul>
            <?php foreach ($header_links as $link): ?>
                <ul>
                    <li>
                        <a href="<?= e($link['url']) ?>" <?php if ($current_path == trim($link['url'], '/')): ?> data-active <?php endif ?>><?= e($link['title']) ?></a>
                    </li>
                </ul>
            <?php endforeach ?>
        </nav>
        <div class="menu-options">
            <button class="menu-button pointer" onclick="document.body.toggleAttribute('data-nav-open')">
                <?= $this->include('icons/menu.svg') ?>
            </button>
            <button class="pointer" onclick="search_dialog.showModal()">
                <?= $this->include('icons/glass.svg') ?>
            </button>
        </div>
    </div>
</header>
<dialog id="search-dialog" class="search">
    <form action="/<?= e(setting('blog_url')) ?>" method="get">
        <input id="search-input" type="text" name="search" placeholder="<?= t('search') ?>" value="<?= e($_GET['search'] ?? '') ?>"/>
        <label class="pointer">
            <input type="submit" class="hidden"/>
            <?= $this->include('icons/glass.svg') ?>
        </label>
    </form>
</dialog>
<div class="nav-background" onclick="document.body.toggleAttribute('data-nav-open')"></div>
<script>
    var search_dialog = document.getElementById('search-dialog');

    search_dialog.addEventListener('click', e => {
        if (e.target === search_dialog) {
            search_dialog.close();
        }
    });
</script>
