<?php $this->extend('admin/base.php') ?>

<?php $this->sectionStart('title') ?>
    <?= t('link') ?>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('content') ?>
    <form id="link-form" class="content">
        <div>
            <div class="page-title">
                <?= $this->include('admin/partials/menu_btn.php') ?>
                <h2><?= t('link') ?></h2>
            </div>
            <div class="buttons">
                <?php if (\Aurora\Core\Helper::isValidId($link['id'] ?? false)): ?>
                    <button type="button" class="delete" onclick="remove(this);" <?php if (!\Aurora\App\Permission::can('edit_links')): ?> disabled <?php endif ?>>
                        <?= $this->include('icons/trash.svg') ?>
                    </button>
                    <button type="button" onclick="window.open(<?= e(js($link['url'])) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?></button>
                <?php endif ?>
                <button type="submit" <?php if (!\Aurora\App\Permission::can('edit_links')): ?> disabled <?php endif ?>><?= t('save') ?></button>
            </div>
        </div>
        <div class="small-form">
            <div class="card v-spacing">
                <div class="input-group">
                    <label for="title"><?= t('title') ?></label>
                    <input id="title" name="title" type="text" value="<?= e($link['title'] ?? '') ?>" data-char-count/>
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
                        <button type="button" class="slider" onclick="get('#status').click()"></button>
                    </div>
                </div>
                <?php if (\Aurora\Core\Helper::isValidId($link['id'] ?? false)): ?>
                    <div class="extra-data">
                        <span><?= t('id') ?>: <?= e($link['id']) ?></span>
                    </div>
                <?php endif ?>
            </div>
            <input type="hidden" name="csrf" value="<?= e($this->csrfToken()) ?>"/>
        </div>
    </form>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('extra') ?>
    <script>
        window.id = <?= js($link['id'] ?? '') ?>;

        document.getElementById('link-form').addEventListener('submit', event => {
            event.preventDefault();
            Form.send('/admin/links/save?id=' + window.id, 'link-form', event.target.querySelector('[type="submit"]')).then(res => {
                if (typeof res?.id !== 'undefined') {
                    window.id = res.id;
                }
            });
        });

        function remove(btn) {
            return confirm(LANG.delete_confirm.sprintf(<?= js($link['title'] ?? '') ?>)) && Form.send('/admin/links/remove', null, btn, {
                csrf: csrf_token,
                id: window.id,
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
