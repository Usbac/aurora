<!DOCTYPE html>
<html lang="<?= e(\Aurora\System\Container::get('language')->getCode()) ?>">
<head>
    <title><?= t('restore_your_password') . ' - ' . e(setting('title')) ?></title>
    <?= $this->include('admin/partials/head.php') ?>
</head>
<body class="login-page">
    <?= $this->include('admin/partials/snackbar.php') ?>
    <form id="restore-form" class="card v-spacing">
        <div class="input-group">
            <label for="password"><?= t('new_password') ?></label>
            <input id="password" type="password" name="password" value=""/>
        </div>
        <div class="input-group">
            <label for="password-confirm"><?= t('password_confirm') ?></label>
            <input id="password-confirm" type="password" name="password_confirm" value=""/>
        </div>
        <input type="hidden" name="hash" value="<?= e($_GET['hash']) ?>"/>
        <input type="hidden" name="csrf" value="<?= e($this->csrfToken()) ?>"/>
        <button type="submit"><?= t('restore_your_password') ?></button>
    </form>
</body>
</html>
<script>
    document.getElementById('restore-form').addEventListener('submit', event => {
        event.preventDefault();
        Form.send('/admin/password_restore', 'restore-form', event.target.querySelector('[type="submit"]')).then(res => {
            if (res.success) {
                setTimeout(() => location.href = '/admin/dashboard', 2000);
            }
        });
    });
</script>
