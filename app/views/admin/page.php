<?php $this->extend('admin/base.php') ?>

<?php $this->sectionStart('title') ?>
    <?= t('page') . ' - ' . e(setting('title')) ?>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('content') ?>
    <form id="page-form" class="content">
        <?php $can_edit_page = \Aurora\App\Permission::can('edit_pages'); ?>
        <div>
            <div class="page-title">
                <?= $this->include('admin/partials/menu_btn.php') ?>
                <h2><?= t('page') ?></h2>
            </div>
            <div class="buttons">
                <?php if (\Aurora\System\Helper::isValidId($page['id'] ?? false)): ?>
                    <button type="button" class="delete" onclick="remove(this);" <?php if (!$can_edit_page): ?> disabled <?php endif ?>>
                        <?= $this->include('icons/trash.svg') ?>
                    </button>
                    <button type="button" onclick="window.open(<?= e(js($this->url($page['slug']))) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?></button>
                <?php endif ?>
                <button type="submit" <?php if (!$can_edit_page): ?> disabled <?php endif ?>><?= t('save') ?></button>
            </div>
        </div>
        <div class="grid grid-two-columns">
            <div class="grid">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label for="title"><?= t('title') ?></label>
                        <input id="title" type="text" name="title" value="<?= e($page['title'] ?? '') ?>" char-count/>
                    </div>
                </div>
                <div id="page-editor">
                    <textarea id="html" name="html"><?= $page['html'] ?? '' ?></textarea>
                </div>
            </div>
            <div class="grid">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label for="slug"><?= t('slug') ?></label>
                        <input id="slug" name="slug" type="text" placeholder="lorem-ipsum" value="<?= e($page['slug'] ?? '') ?>" maxlength="255" char-count/>
                        <a id="page-link" target="_blank"></a>
                    </div>
                    <?php if (\Aurora\System\Helper::isValidId($page['id'] ?? false)): ?>
                        <div class="extra-data">
                            <span><?= t('id') ?>: <?= e($page['id']) ?></span>
                            <?php if (setting('views_count')): ?>
                                <span><?= t('number_views') ?>: <?= e($page['views']) ?></span>
                            <?php endif ?>
                        </div>
                    <?php endif ?>
                </div>
                <div class="card v-spacing">
                    <div class="input-group">
                        <label for="status"><?= t('published') ?></label>
                        <div class="switch">
                            <input id="status" name="status" type="checkbox" <?php if ($page['status'] ?? false): ?> checked <?php endif ?>>
                            <button type="button" class="slider" onclick="get('#status').click()"></button>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="static"><?= t('static') ?></label>
                        <div class="switch">
                            <input id="static" name="static" type="checkbox" <?php if ($page['static'] ?? false): ?> checked <?php endif ?> oninput="toggleEditor(!this.checked)">
                            <button type="button" class="slider" onclick="get('#static').click()"></button>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="static-file"><?= t('static_file') ?></label>
                        <select id="static-file" name="static_file">
                            <option value=""></option>
                            <?php foreach ($view_files as $file): ?>
                                <option value="<?= e($file) ?>" <?php if (($page['static_file'] ?? '') == $file): ?> selected <?php endif ?>><?= e($file) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="card v-spacing">
                    <div class="input-group">
                        <label for="meta-title"><?= t('meta_title') ?></label>
                        <input id="meta-title" name="meta_title" type="text" placeholder="lorem ipsum" value="<?= e($page['meta_title'] ?? '') ?>" char-count/>
                    </div>
                    <div class="input-group">
                        <label for="meta-description"><?= t('meta_description') ?></label>
                        <textarea id="meta-description" name="meta_description" char-count><?= e($page['meta_description'] ?? '') ?></textarea>
                    </div>
                    <div class="input-group">
                        <label for="canonical-url"><?= t('canonical_url') ?></label>
                        <input id="canonical-url" name="canonical_url" type="text" placeholder="<?= e($this->url('/about')) ?>" value="<?= e($page['canonical_url'] ?? '') ?>"/>
                    </div>
                </div>
            </div>
            <input type="hidden" name="csrf" value="<?= e($this->csrfToken()) ?>"/>
        </div>
    </form>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('extra') ?>
    <?= setting('editor_code') ?>
    <script>
        window.id = <?= js($page['id'] ?? '') ?>;

        document.getElementById('page-form').addEventListener('submit', event => {
            event.preventDefault();
            Form.send('/admin/pages/save?id=' + window.id, 'page-form', event.target.querySelector('[type="submit"]')).then(res => {
                updateUrl();
                if (typeof res?.id !== 'undefined') {
                    window.id = res.id;
                }
            });
        });

        function remove(btn) {
            if (!confirm(LANG.delete_confirm.sprintf(<?= js($page['title'] ?? '') ?>))) {
                return;
            }

            Form.send('/admin/pages/remove', null, btn, {
                csrf: csrf_token,
                id: window.id,
            }).then(res => {
                if (res.success) {
                    setTimeout(() => history.back(), 2000);
                }
            });
        }

        function updateUrl() {
            let url = <?= js($this->url()) ?> + '/' + get('#slug').value;
            get('#page-link').innerHTML = url;
            get('#page-link').setAttribute('href', url);
        }

        function toggleEditor(show) {
            get('#page-editor').style.display = show ? 'flex' : 'none';
        }

        window.addEventListener('load', () => {
            Form.initCharCounters();
            updateUrl();
            toggleEditor(<?= js(!($page['static'] ?? false)) ?>);
        });
    </script>
<?php $this->sectionEnd() ?>
