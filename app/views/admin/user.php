<?php $this->extend('admin/base.php') ?>

<?php $current_user = !empty($user) && $user['id'] == $_SESSION['user']['id'] ?>
<?php $title = t($current_user ? 'your_user' : 'user'); ?>

<?php $this->sectionStart('title') ?>
    <?= $title ?>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('content') ?>
    <form id="user-form" class="content">
        <?php $can_edit_user = \Aurora\App\Permission::edit_user($user); ?>
        <div>
            <div class="page-title">
                <?= $this->include('admin/partials/menu_btn.php') ?>
                <h2><?= $title ?></h2>
            </div>
            <div class="buttons">
                <?php if (\Aurora\Core\Helper::isValidId($user['id'] ?? false)): ?>
                    <?php if (!$current_user): ?>
                        <button type="button" class="delete" onclick="remove(this);" <?php if (!$can_edit_user): ?> disabled <?php endif ?>>
                            <?= $this->include('icons/trash.svg') ?>
                        </button>
                        <?php if (\Aurora\App\Permission::impersonate($user)): ?>
                            <button type="button" onclick="if (confirm(LANG.impersonate_confirm)) location.href = '/admin/users/impersonate?id=' + <?= e(js($user['id'])) ?>"><?= $this->include('icons/users.svg') ?></button>
                        <?php endif ?>
                    <?php endif ?>
                    <button type="button" onclick="window.open(<?= e(js('/' . setting('blog_url') . '/author/' . $user['slug'])) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?></button>
                <?php endif ?>
                <button type="submit" <?php if (!$can_edit_user): ?> disabled <?php endif ?>><?= t('save') ?></button>
            </div>
        </div>
        <div class="grid grid-two-columns wide">
            <div>
                <div class="user-image pointer">
                    <?php if (!empty($user['image'])): ?>
                        <img src="<?= e($this->getContentUrl($user['image'])) ?>"/>
                    <?php else: ?>
                        <img src="/public/assets/no-image.svg" class="empty-img"/>
                    <?php endif ?>
                </div>
                <input id="user-image-input" type="hidden" name="image" value="<?= e($user['image'] ?? '') ?>"/>
                <?php if (\Aurora\Core\Helper::isValidId($user['id'] ?? false)): ?>
                    <div class="extra-info">
                        <p><?= t('id') ?>: <?= e($user['id']) ?></p>
                        <p><?= t('number_posts') ?>: <?= e($user['posts']) ?></p>
                        <p><?= t('last_active') ?>: <?= e($this->dateFormat($user['last_active'])) ?></p>
                    </div>
                <?php endif ?>
            </div>
            <div class="grid">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label for="name"><?= t('name') ?></label>
                        <input id="name" name="name" type="text" value="<?= e($user['name'] ?? '') ?>"/>
                    </div>
                    <div class="input-group">
                        <label for="slug"><?= t('slug') ?></label>
                        <input id="slug" name="slug" type="text" value="<?= e($user['slug'] ?? '') ?>" maxlength="255" data-char-count/>
                    </div>
                    <div class="input-group">
                        <label for="email"><?= t('email') ?></label>
                        <input id="email" name="email" type="text" value="<?= e($user['email'] ?? '') ?>" maxlength="255"/>
                    </div>
                    <div class="input-group">
                        <label for="bio"><?= t('bio') ?></label>
                        <textarea id="bio" name="bio" data-char-count><?= e($user['bio'] ?? '') ?></textarea>
                    </div>
                    <div class="input-group">
                        <label for="role"><?= t('role') ?></label>
                        <select id="role" name="role">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= e($role['level']) ?>" <?php if (!empty($user) && $role['level'] == $user['role']): ?> selected <?php endif ?>><?= t($role['slug']) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label><?= t('status') ?></label>
                        <div class="switch">
                            <input id="status" name="status" type="checkbox" <?php if ($user['status'] ?? false): ?> checked <?php endif ?> <?php if ($current_user): ?> disabled <?php endif ?>>
                            <button type="button" class="slider" onclick="get('#status').click()"></button>
                        </div>
                    </div>
                </div>
                <div class="card v-spacing">
                    <h3><?= t('password') ?></h3>
                    <div class="input-group">
                        <label for="password"><?= t('new_password') ?></label>
                        <input id="password" name="password" type="password" value=""/>
                    </div>
                    <div class="input-group">
                        <label for="password-confirm"><?= t('password_confirm') ?></label>
                        <input id="password-confirm" name="password_confirm" type="password" value=""/>
                    </div>
                </div>
            </div>
            <input type="hidden" name="csrf" value="<?= e($this->csrfToken()) ?>"/>
        </div>
        <div id="image-dialog" class="dialog image-dialog">
            <div></div>
        </div>
    </form>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('extra') ?>
    <script>
        window.id = <?= js($user['id'] ?? '') ?>;

        document.getElementById('user-form').addEventListener('submit', event => {
            event.preventDefault();
            updateSlug();
            Form.send('/admin/users/save?id=' + window.id, 'user-form', event.target.querySelector('[type="submit"]')).then(res => {
                if (typeof res?.id !== 'undefined') {
                    window.id = res.id;
                }
            });
        });

        function remove(btn) {
            return confirm(LANG.delete_confirm.sprintf(<?= js($user['name'] ?? '') ?>)) && Form.send('/admin/users/remove', null, btn, {
                csrf: csrf_token,
                id: window.id,
            }).then(res => {
                if (res.success) {
                    setTimeout(() => history.back(), 2000);
                }
            });
        }

        function updateSlug() {
            let slug = get('#slug');
            if (!slug.value.trim()) {
                slug.value = get('#name').value.toSlug();
                slug.dispatchEvent(new Event('input'));
            }
        }

        window.addEventListener('load', () => {
            ImageDialog.init(get('#image-dialog'), get('#user-image-input'), get('.user-image > img'), <?= js(\Aurora\Core\Kernel::config('content')) ?>);
            Form.initCharCounters();
        });
    </script>
<?php $this->sectionEnd() ?>
