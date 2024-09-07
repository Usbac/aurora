<!DOCTYPE html>
<html lang="<?= e(\Aurora\Core\Container::get('language')->getCode()) ?>">
<head>
    <title><?= t('sign_in') . ' - ' . e(setting('title')) ?></title>
    <?= $this->include('admin/partials/head.php') ?>
</head>
<body class="login-page">
    <?= $this->include('admin/partials/snackbar.php') ?>
    <form id="login-form" class="card v-spacing">
        <?php if (!empty(setting('logo'))): ?>
            <img src="<?= e($this->getContentUrl(setting('logo'))) ?>"/>
        <?php endif ?>
        <div class="input-group">
            <label for="email"><?= t('email') ?></label>
            <input id="email" type="email" name="email" placeholder="johndoe@mail.com" value="" maxlength="255"/>
        </div>
        <div class="input-group">
            <label for="password"><?= t('password') ?></label>
            <input id="password" type="password" name="password" value=""/>
        </div>
        <input type="hidden" name="csrf" value="<?= e($this->csrfToken()) ?>"/>
        <button type="submit"><?= t('sign_in') ?></button>
        <button type="button" class="pointer light" onclick="get('#login-form').classList.add('hidden'); get('#restore-form').classList.remove('hidden');"><?= t('forgot_password') ?></button>
    </form>
    <form id="restore-form" class="card v-spacing hidden">
        <?php if (!empty(setting('logo'))): ?>
            <img src="<?= e($this->getContentUrl(setting('logo'))) ?>"/>
        <?php endif ?>
        <div class="input-group">
            <label for="restore-email"><?= t('email') ?></label>
            <input id="restore-email" type="email" name="email" value="" maxlength="255"/>
        </div>
        <input type="hidden" name="csrf" value="<?= e($this->csrfToken()) ?>"/>
        <button type="submit"><?= t('get_new_password') ?></button>
        <button class="pointer light" onclick="get('#login-form').classList.remove('hidden'); get('#restore-form').classList.add('hidden');"><?= t('go_back') ?></button>
    </form>
</body>
</html>
<script>
    document.getElementById('login-form').addEventListener('submit', event => {
        event.preventDefault();
        Form.send('/admin/login', 'login-form', event.target.querySelector('[type="submit"]')).then(res => {
            if (res.success) {
                setTimeout(() => location.reload(), 3000);
            }
        });
    });

    document.getElementById('restore-form').addEventListener('submit', event => {
        event.preventDefault();
        Form.send('/admin/send_password_restore', 'restore-form', event.target.querySelector('[type="submit"]')).then(res => {
            if (res.success) {
                get('#restore-email').value = '';
            }
        });
    });
</script>
