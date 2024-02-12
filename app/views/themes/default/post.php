<!DOCTYPE html>
<html lang="<?= e(\Aurora\System\Container::get('language')->getCode()) ?>">
<head>
    <title><?= e($post['title'] . ' - ' . setting('title')) ?></title>
    <?= $this->include('themes/default/partials/head.php') ?>
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= e($post[empty($post['meta_title']) ? 'title' : 'meta_title']) ?>"/>
    <meta property="og:description" content="<?= e($post[empty($post['meta_description']) ? 'description' : 'meta_description']) ?>"/>
    <meta name="author" content="<?= e($post['user_name'] ?? '') ?>"/>
    <?php foreach ($post['tags'] as $tag): ?>
        <meta property="article:tag" content="<?= e($tag) ?>"/>
    <?php endforeach ?>
    <link rel="canonical" href="<?= e(empty($post['canonical_url']) ? $this->url($_SERVER['REQUEST_URI']) : $post['canonical_url']) ?>"/>
</head>
<body>
    <?= $this->include('themes/default/partials/header.php') ?>
    <main class="post">
        <section class="narrow post-top">
            <div class="post-tags">
                <?php foreach ($post['tags'] as $tag_slug => $tag_title): ?>
                    <a href="<?= e('/' . setting('blog_url') . '/tag/' . $tag_slug) ?>"><?= e($tag_title) ?></a>
                <?php endforeach ?>
            </div>
            <h1><?= e($post['title']) ?></h1>
            <?php if (!empty($post['description'])): ?>
                <span><?= e($post['description']) ?></span>
            <?php endif ?>
            <?php if (!empty($post['image'])): ?>
                <img src="<?= e($this->getContentUrl($post['image'])) ?> " alt="<?= e($post['image_alt'] ?? '') ?>"/>
            <?php endif ?>
            <div class="meta">
                <?php if ($post['user_id']): ?>
                    <div>
                        <?php $user_url = '/' . setting('blog_url') . '/author/' . $post['user_slug'] ?>
                        <a href="<?= e($user_url) ?>" class="pointer">
                            <img src="<?= !empty($post['user_image']) ? e($this->getContentUrl($post['user_image'])) : '/public/assets/user.svg' ?>"/>
                        </a>
                        <a href="<?= e($user_url) ?>"><?= e($post['user_name']) ?></a>
                    </div>
                <?php endif ?>
                <span><?= e($this->dateFormat($post['published_at'])) ?></span>
                <span><?= e($this->getReadTime($post['html'])) ?> <?= t('minutes_read') ?></span>
            </div>
        </section>
        <section class="narrow post-html">
            <article><?= $post['html'] ?></article>
        </section>
        <?php if (!empty(setting('post_code'))): ?>
            <section>
                <?= setting('post_code') ?>
            </section>
        <?php endif ?>
        <?php if (!empty($related_posts)): ?>
            <section class="post-related">
                <h2><?= t('related_posts') ?></h2>
                <div>
                    <?php foreach ($related_posts as $related): ?>
                        <?php $related_post_url = '/' . setting('blog_url') . '/' . $related['slug'] ?>
                        <div class="related-card">
                            <a href="<?= e($related_post_url) ?>">
                                <img src="<?= e($this->getContentUrl($related['image'])) ?>" alt="<?= e($related['image_alt'] ?? '') ?>" class="pointer"/>
                            </a>
                            <?php if (!$related['status']): ?>
                                <span class="label red"><?= t('draft') ?></span>
                            <?php elseif ($related['published_at'] > time()): ?>
                                <span class="label"><?= t('scheduled') ?></span>
                            <?php endif ?>
                            <h3>
                                <a href="<?= e($related_post_url) ?>"><?= e($related['title']) ?></a>
                            </h3>
                            <div class="meta">
                                <span><?= e($this->dateFormat($related['published_at'])) ?></span>
                                <span><?= e($this->getReadTime($related['html'])) ?> <?= t('minutes_read') ?></span>
                            </div>
                            <div><?= e($related['description']) ?></div>
                        </div>
                    <?php endforeach ?>
                </div>
            </section>
        <?php endif ?>
    </main>
    <?= $this->include('themes/default/partials/footer.php') ?>
</body>
</html>
