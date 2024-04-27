<?php $this->extend('admin/base.php') ?>

<?php $this->sectionStart('title') ?>
    <?php $current_user = !empty($user) && $user['id'] == $_SESSION['user']['id'] ?>
    <?php $title = t($current_user ? 'your_user' : 'user'); ?>
    <?= "$title - " . e(setting('title')) ?>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('content') ?>
    <?php $can_edit_user = \Aurora\App\Permission::can('edit_users'); ?>
    <div>
        <div class="page-title">
            <?= $this->include('admin/partials/menu_btn.php') ?>
            <h2><?= $title ?></h2>
        </div>
        <div class="buttons">
            <?php if (\Aurora\System\Helper::isValidId($user['id'] ?? false)): ?>
                <?php if (!$current_user): ?>
                    <button class="delete" onclick="remove(this);" <?php if (!$can_edit_user): ?> disabled <?php endif ?>>
                        <?= $this->include('icons/trash.svg') ?>
                    </button>
                    <?php if (\Aurora\App\Permission::impersonate($user)): ?>
                        <button onclick="if (confirm(<?= e(js(t('impersonate_confirm', false))) ?>)) location.href = '/admin/users/impersonate?id=' + <?= e(js($user['id'])) ?>"><?= $this->include('icons/users.svg') ?></button>
                    <?php endif ?>
                <?php endif ?>
                <button onclick="window.open(<?= e(js('/' . setting('blog_url') . '/author/' . $user['slug'])) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?></button>
            <?php endif ?>
            <button onclick="save()" <?php if (!$can_edit_user): ?> disabled <?php endif ?>><?= t('save') ?></button>
        </div>
    </div>
    <div id="user-form" class="grid grid-two-columns wide">
        <div>
            <div class="user-image pointer">
                <?php if (!empty($user['image'])): ?>
                    <img src="<?= e($this->getContentUrl($user['image'])) ?>"/>
                <?php else: ?>
                    <img src="/public/assets/no-image.svg" class="empty-img"/>
                <?php endif ?>
            </div>
            <input id="user-image-input" type="hidden" name="image" value="<?= e($user['image'] ?? '') ?>"/>
            <?php if (\Aurora\System\Helper::isValidId($user['id'] ?? false)): ?>
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
                    <input id="slug" name="slug" type="text" value="<?= e($user['slug'] ?? '') ?>" maxlength="255" char-count/>
                </div>
                <div class="input-group">
                    <label for="email"><?= t('email') ?></label>
                    <input id="email" name="email" type="text" value="<?= e($user['email'] ?? '') ?>" maxlength="255"/>
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
                        <button class="slider" onclick="get('#status').click()"></button>
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
    <dialog id="image-dialog" class="image-dialog"></dialog>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('extra') ?>
    <script>
        window.id = <?= js($user['id'] ?? '') ?>;

        function save() {
            updateSlug();
            Form.send('/admin/users/save?id=' + window.id, 'user-form').then(res => {
                if (typeof res?.id !== 'undefined') {
                    window.id = res.id;
                }
            });
        }

        function remove(btn) {
            if (!confirm(LANG.delete_confirm.sprintf(<?= js($user['name'] ?? '') ?>))) {
                return;
            }

            Form.send('/admin/users/remove', null, btn, {
                csrf: <?= js($this->csrfToken()) ?>,
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
            ImageDialog.init(get('#image-dialog'), get('#user-image-input'), get('.user-image > img'), <?= js(\Aurora\System\Kernel::config('content')) ?>);
            Form.initCharCounters();
        });
    </script>
<?php $this->sectionEnd() ?>
