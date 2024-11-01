<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= e(setting('meta_description')) ?>">
<meta name="theme-color" content="">
<link rel="icon" type="image/x-icon" href="/public/assets/favicon.ico">
<link rel="stylesheet" href="<?= e($this->getFileQuery('/public/assets/css/admin/main.css')) ?>">
<link id="css-dark" rel="stylesheet" href="<?= e($this->getFileQuery('/public/assets/css/admin/dark.css')) ?>" <?php if (($_COOKIE['theme'] ?? '') !== 'dark'): ?> disabled <?php endif ?>>
<script>
    window.LANG = <?= js(t()) ?>;
    window.csrf_token = <?= js($this->csrfToken()) ?>;
</script>
<script src="<?= e($this->getFileQuery('/public/assets/js/admin.js')) ?>"></script>
