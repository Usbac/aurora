<!DOCTYPE html>
<html lang="<?= e(lang()) ?>">
<head>
    <title><?= t('page') . ' - ' . e(setting('title')) ?></title>
    <?= $this->include('admin/partials/head.php') ?>
</head>
<body class="admin">
    <?= $this->include('admin/partials/nav.php') ?>
    <?php $can_edit_page = \Aurora\App\Permission::can('edit_pages'); ?>
    <div class="content">
        <div>
            <div class="page-title">
                <?= $this->include('admin/partials/menu_btn.php') ?>
                <h2><?= t('page') ?></h2>
            </div>
            <div class="buttons">
                <?php if (\Aurora\System\Helper::isValidId($page['id'] ?? false)): ?>
                    <button class="delete" onclick="remove(this);" <?php if (!$can_edit_page): ?> disabled <?php endif ?>>
                        <?= $this->include('icons/trash.svg') ?>
                    </button>
                    <button onclick="window.open(<?= e(js(url($page['slug']))) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?></button>
                <?php endif ?>
                <button id="save" onclick="save();" <?php if (!$can_edit_page): ?> disabled <?php endif ?>><?= t('save') ?></button>
            </div>
        </div>
        <div id="page-form" class="grid grid-two-columns">
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
                        <span class="form-extra-data"><?= t('number_views') ?>: <?= e($page['views']) ?></span>
                    <?php endif ?>
                </div>
                <div class="card v-spacing">
                    <div class="input-group">
                        <label for="status"><?= t('published') ?></label>
                        <div class="switch">
                            <input id="status" name="status" type="checkbox" <?php if ($page['status'] ?? false): ?> checked <?php endif ?>>
                            <button class="slider" onclick="get('#status').click()"></button>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="static"><?= t('static') ?></label>
                        <div class="switch">
                            <input id="static" name="static" type="checkbox" <?php if ($page['static'] ?? false): ?> checked <?php endif ?> oninput="toggleEditor(!this.checked)">
                            <button class="slider" onclick="get('#static').click()"></button>
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
                        <input id="canonical-url" name="canonical_url" type="text" placeholder="<?= e(url('/about')) ?>" value="<?= e($page['canonical_url'] ?? '') ?>"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?= setting('editor_code') ?>
<script>
    window.id = <?= js($page['id'] ?? '') ?>;

    function save() {
        Form.send('/admin/pages/save?id=' + window.id, 'page-form').then(res => {
            updateUrl();
            if (typeof res?.id !== 'undefined') {
                window.id = res.id;
            }
        });
    }

    function remove(btn) {
        if (!confirm(<?= js(t('delete_confirm', false)) ?>.sprintf(<?= js($page['title'] ?? '') ?>))) {
            return;
        }

        Form.send('/admin/pages/remove/' + window.id, null, btn).then(res => {
            if (res.success) {
                setTimeout(() => history.back(), 2000);
            }
        });
    }

    function updateUrl() {
        let url = <?= js(url()) ?> + '/' + get('#slug').value;
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
