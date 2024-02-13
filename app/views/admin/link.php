<?php $this->extend('admin/base.php') ?>

<?php $this->sectionStart('title') ?>
    <?= t('link') . ' - ' . e(setting('title')) ?>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('content') ?>
    <div>
        <div class="page-title">
            <?= $this->include('admin/partials/menu_btn.php') ?>
            <h2><?= t('link') ?></h2>
        </div>
        <div class="buttons">
            <?php if (\Aurora\System\Helper::isValidId($link['id'] ?? false)): ?>
                <button class="delete" onclick="remove(this);" <?php if (!\Aurora\App\Permission::can('edit_links')): ?> disabled <?php endif ?>>
                    <?= $this->include('icons/trash.svg') ?>
                </button>
                <button onclick="window.open(<?= e(js($link['url'])) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?></button>
            <?php endif ?>
            <button onclick="save()" <?php if (!\Aurora\App\Permission::can('edit_links')): ?> disabled <?php endif ?>><?= t('save') ?></button>
        </div>
    </div>
    <div id="link-form" class="small-form">
        <div class="card v-spacing">
            <div class="input-group">
                <label for="title"><?= t('title') ?></label>
                <input id="title" name="title" type="text" value="<?= e($link['title'] ?? '') ?>" char-count/>
            </div>
            <div class="input-group">
                <label for="url"><?= t('url') ?></label>
                <input id="url" name="url" type="text" value="<?= e($link['url'] ?? '') ?>"/>
            </div>
            <div class="input-group">
                <label for="order"><?= t('order') ?></label>
                <input id="order" name="order" type="number" value="<?= e($link['order'] ?? '') ?>"/>
            </div>
            <div class="input-group">
                <label><?= t('status') ?></label>
                <div class="switch">
                    <input id="status" name="status" type="checkbox" <?php if ($link['status'] ?? false): ?> checked <?php endif ?>>
                    <button class="slider" onclick="get('#status').click()"></button>
                </div>
            </div>
            <?php if (\Aurora\System\Helper::isValidId($link['id'] ?? false)): ?>
                <div class="extra-data">
                    <span><?= t('id') ?>: <?= e($link['id']) ?></span>
                </div>
            <?php endif ?>
        </div>
        <input type="hidden" name="csrf" value="<?= e($this->csrfToken()) ?>"/>
    </div>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('extra') ?>
    <script>
        window.id = <?= js($link['id'] ?? '') ?>;

        function save() {
            Form.send('/admin/links/save?id=' + window.id, 'link-form').then(res => {
                if (typeof res?.id !== 'undefined') {
                    window.id = res.id;
                }
            });
        }

        function remove(btn) {
            if (!confirm(<?= js(t('delete_confirm', false)) ?>.sprintf(<?= js($link['title'] ?? '') ?>))) {
                return;
            }

            Form.send('/admin/links/remove/' + window.id, null, btn, {
                csrf: <?= js($this->csrfToken()) ?>,
            }).then(res => {
                if (res.success) {
                    setTimeout(() => history.back(), 2000);
                }
            });
        }

        window.addEventListener('load', () => {
            Form.initCharCounters();
        });
    </script>
<?php $this->sectionEnd() ?>
