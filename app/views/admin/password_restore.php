<!DOCTYPE html>
<html lang="<?= e($this->lang()) ?>">
<head>
    <title><?= t('restore_your_password') . ' - ' . e(setting('title')) ?></title>
    <?= $this->include('admin/partials/head.php') ?>
</head>
<body class="login-page">
    <div id="restore-form" class="v-spacing">
        <div class="input-group">
            <label for="password"><?= t('new_password') ?></label>
            <input id="password" type="password" name="password" value=""/>
        </div>
        <div class="input-group">
            <label for="password-confirm"><?= t('password_confirm') ?></label>
            <input id="password-confirm" type="password" name="password_confirm" value=""/>
        </div>
        <input type="hidden" name="hash" value="<?= e($_GET['hash']) ?>"/>
        <button onclick="passwordRestore()"><?= t('restore_your_password') ?></button>
    </div>
</body>
</html>
<script>
    function passwordRestore() {
        Form.send('/admin/password_restore', 'restore-form').then(res => {
            if (res.success) {
                setTimeout(() => location.href = '/admin/dashboard', 2000);
            }
        });
    }
</script>
