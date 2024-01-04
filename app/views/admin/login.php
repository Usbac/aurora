<!DOCTYPE html>
<html lang="<?= e(lang()) ?>">
<head>
    <title><?= t('sign_in') . ' - ' . e(setting('title')) ?></title>
    <?= $this->include('admin/partials/head.php') ?>
</head>
<body class="login-page">
    <?= $this->include('admin/partials/snackbar.php') ?>
    <div id="login-form" class="card v-spacing">
        <?php if (!empty(setting('logo'))): ?>
            <img src="<?= e($this->getContentUrl(setting('logo'))) ?>"/>
        <?php endif ?>
        <div class="input-group">
            <label for="email"><?= t('email') ?></label>
            <input id="email" type="email" name="email" placeholder="johndoe@gmail.com" value="" maxlength="255"/>
        </div>
        <div class="input-group">
            <label for="password"><?= t('password') ?></label>
            <input id="password" type="password" name="password" value=""/>
        </div>
        <button onclick="Form.send('/admin/login', 'login-form').then(loginHandler);"><?= t('sign_in') ?></button>
        <button class="pointer light" onclick="get('#login-form').classList.add('hidden'); get('#restore-form').classList.remove('hidden');"><?= t('forgot_password') ?></button>
    </div>
    <div id="restore-form" class="card v-spacing hidden">
        <?php if (!empty(setting('logo'))): ?>
            <img src="<?= e($this->getContentUrl(setting('logo'))) ?>"/>
        <?php endif ?>
        <div class="input-group">
            <label for="restore-email"><?= t('email') ?></label>
            <input id="restore-email" type="email" name="email" value="" maxlength="255"/>
        </div>
        <button onclick="sendPasswordRestore()"><?= t('get_new_password') ?></button>
        <button class="pointer light" onclick="get('#login-form').classList.remove('hidden'); get('#restore-form').classList.add('hidden');"><?= t('go_back') ?></button>
    </div>
</body>
</html>
<script>
    function loginHandler(res) {
        if (res.success) {
            setTimeout(() => location.reload(), 1000);
        }
    }

    function sendPasswordRestore() {
        Form.send('/admin/send_password_restore', 'restore-form').then(res => {
            if (res.success) {
                get('#restore-email').value = '';
            }
        });
    }
</script>
