<!doctype html>
<html lang="<?= e(\Aurora\Core\Container::get('language')->getCode()) ?>">
<head>
    <title><?= e("$title - " . setting('title')) ?></title>
    <?= $this->include('themes/default/partials/head.php') ?>
    <meta property="og:title" content="<?= e(empty($meta_title) ? $title : $meta_title) ?>"/>
    <meta property="og:description" content="<?= e(empty($meta_description) ? setting('meta_description') : $meta_description) ?>"/>
    <meta property="og:type" content="website"/>
    <link rel="canonical" href="<?= e(empty($canonical_url) ? $this->url($_SERVER['REQUEST_URI']) : $canonical_url) ?>"/>
</head>
<body>
    <?= $this->include('themes/default/partials/header.php') ?>
    <main><?= $html ?></main>
    <?= $this->include('themes/default/partials/footer.php') ?>
</body>
</html>
