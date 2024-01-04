<!DOCTYPE html>
<html lang="<?= e(lang()) ?>">
<head>
    <title><?= t('tag') . ' - ' . e(setting('title')) ?></title>
    <?= $this->include('admin/partials/head.php') ?>
</head>
<body class="admin">
    <?= $this->include('admin/partials/nav.php') ?>
    <div class="content">
        <div>
            <div class="page-title">
                <?= $this->include('admin/partials/menu_btn.php') ?>
                <h2><?= t('tag') ?></h2>
            </div>
            <div class="buttons">
                <?php if (\Aurora\System\Helper::isValidId($tag['id'] ?? false)): ?>
                    <button class="delete" onclick="remove(this);" <?php if (!\Aurora\App\Permission::can('edit_tags')): ?> disabled <?php endif ?>>
                        <?= $this->include('icons/trash.svg') ?>
                    </button>
                    <button onclick="window.open(<?= e(js('/' . setting('blog_url') . '/tag/' . $tag['slug'])) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?></button>
                <?php endif ?>
                <button onclick="save()" <?php if (!\Aurora\App\Permission::can('edit_tags')): ?> disabled <?php endif ?>><?= t('save') ?></button>
            </div>
        </div>
        <div id="tag-form" class="grid small-form">
            <div class="card v-spacing">
                <div class="input-group">
                    <label for="name"><?= t('name') ?></label>
                    <input id="name" name="name" type="text" value="<?= e($tag['name'] ?? '') ?>"/>
                </div>
                <div class="input-group">
                    <label for="slug"><?= t('slug') ?></label>
                    <input id="slug" name="slug" type="text" value="<?= e($tag['slug'] ?? '') ?>" maxlength="255" char-count/>
                </div>
                <div class="input-group">
                    <label for="description"><?= t('description') ?></label>
                    <textarea id="description" name="description" char-count><?= e($tag['description'] ?? '') ?></textarea>
                </div>
                <?php if (\Aurora\System\Helper::isValidId($tag['id'] ?? false)): ?>
                    <span class="form-extra-data"><?= t('number_posts') ?>: <?= e($tag['posts']) ?></span>
                <?php endif ?>
            </div>
            <div class="card v-spacing">
                <div class="input-group">
                    <label for="meta-title"><?= t('meta_title') ?></label>
                    <input id="meta-title" name="meta_title" type="text" placeholder="lorem ipsum" value="<?= e($tag['meta_title'] ?? '') ?>" char-count/>
                </div>
                <div class="input-group">
                    <label for="meta-description"><?= t('meta_description') ?></label>
                    <textarea id="meta-description" name="meta_description" char-count><?= e($tag['meta_description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<script>
    window.id = <?= js($tag['id'] ?? '') ?>;

    function save() {
        updateSlug();
        Form.send('/admin/tags/save?id=' + window.id, 'tag-form').then(res => {
            if (typeof res?.id !== 'undefined') {
                window.id = res.id;
            }
        });
    }

    function remove(btn) {
        if (!confirm(<?= js(t('delete_confirm', false)) ?>.sprintf(<?= js($tag['name'] ?? '') ?>))) {
            return;
        }

        Form.send('/admin/tags/remove/' + window.id, null, btn).then(res => {
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
        Form.initCharCounters();
    });
</script>
