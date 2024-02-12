<!DOCTYPE html>
<html lang="<?= e(\Aurora\System\Container::get('language')->getCode()) ?>">
<head>
    <title><?= e("$title - " . setting('title')) ?></title>
    <?= $this->include('themes/default/partials/head.php') ?>
    <?php if (isset($tag)): ?>
        <meta property="og:title" content="<?= e($tag[empty($tag['meta_title']) ? 'name' : 'meta_title']) ?>"/>
        <meta property="og:description" content="<?= e($tag[empty($tag['meta_description']) ? 'description' : 'meta_description']) ?>"/>
    <?php else: ?>
        <meta property="og:title" content="<?= e(setting('meta_title')) ?>"/>
        <meta property="og:description" content="<?= e(setting('meta_description')) ?>"/>
    <?php endif ?>
    <link rel="canonical" href="<?= e($this->url($_SERVER['REQUEST_URI'])) ?>"/>
</head>
<body>
    <?= $this->include('themes/default/partials/header.php') ?>
    <main>
        <?php if (!empty($_GET['search'])): ?>
            <section class="blog-title">
                <h2><?= t('searching') ?>: <?= e($_GET['search']) ?></h2>
            </section>
        <?php endif ?>
        <?php if (isset($tag)): ?>
            <section class="blog-title">
                <h2><?= t('tag') ?>: <?= e($tag['name']) ?></h2>
                <span><?= e($tag['description']) ?></span>
            </section>
            <script>var next_page_args = 'tag=' + <?= js($tag['id']) ?>;</script>
        <?php elseif (isset($user)): ?>
            <section class="author">
                <img src="<?= !empty($post['user_image']) ? e($this->getContentUrl($post['user_image'])) : '/public/assets/user.svg' ?>"/>
                <h2><?= e($user['name']) ?></h2>
            </section>
            <script>var next_page_args = 'user=' + <?= e($user['id']) ?>;</script>
        <?php else: ?>
            <script>var next_page_args = '';</script>
        <?php endif ?>
        <section id="posts" class="posts">
            <?= $this->include('themes/default/partials/posts_page.php') ?>
            <?php if (empty($posts)): ?>
                <h3 class="empty"><?= t('no_results') ?></h3>
            <?php endif ?>
        </section>
        <?php if ($next_page): ?>
            <button id="load-posts" class="load-more" onclick="loadNextPage(this, next_page_args)"><?= t('load_more') ?></button>
        <?php endif ?>
    </main>
    <?= $this->include('themes/default/partials/footer.php') ?>
</body>
</html>
<script>
    var next_page = <?= js($current_page + 1) ?>;

    function loadNextPage(btn, args = '') {
        if (!next_page) {
            return;
        }

        btn.classList.add('loading');

        fetch('/api/posts?page=' + next_page + '&' + args)
            .then(res => res.json())
            .then(res => {
                window.history.replaceState(null, null, window.location.pathname + '?page=' + next_page);

                if (!res.next_page) {
                    document.getElementById('load-posts').remove();
                    next_page = false;
                } else {
                    next_page++;
                }

                document.getElementById('posts').insertAdjacentHTML('beforeend', res.html);
            })
            .finally(() => btn.classList.remove('loading'));
    }
</script>
