<!DOCTYPE html>
<html lang="<?= e(\Aurora\Core\Container::get('language')->getCode()) ?>">
<head>
    <title><?= e(setting('title')) ?></title>
    <?= $this->include('themes/default/partials/head.php') ?>
    <link rel="stylesheet" href="<?= e($this->getFileQuery('/public/assets/css/themes/default/information.css')) ?>">
    <link rel="canonical" href="<?= e($this->url($_SERVER['REQUEST_URI'])) ?>"/>
</head>
<body>
    <?php if (setting('logo')): ?>
        <img src="<?= e($this->getContentUrl(setting('logo'))) ?>"/>
    <?php endif ?>
    <?php if (!empty($title)): ?>
        <h1><?= e($title) ?></h1>
    <?php endif ?>
    <?php if (!empty($description)): ?>
        <h2><?= $description ?></h2>
    <?php endif ?>
    <?php if (!empty($subdescription)): ?>
        <p><?= $subdescription ?></p>
    <?php endif ?>
</body>
</html>
