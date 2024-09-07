<?php $this->extend('admin/base.php') ?>

<?php $this->sectionStart('title') ?>
    <?= t('tag') ?>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('content') ?>
<form id="tag-form" class="content">
    <div>
        <div class="page-title">
            <?= $this->include('admin/partials/menu_btn.php') ?>
            <h2><?= t('tag') ?></h2>
        </div>
        <div class="buttons">
            <?php if (\Aurora\System\Helper::isValidId($tag['id'] ?? false)): ?>
                <button type="button" class="delete" onclick="remove(this);" <?php if (!\Aurora\App\Permission::can('edit_tags')): ?> disabled <?php endif ?>>
                    <?= $this->include('icons/trash.svg') ?>
                </button>
                <button type="button" onclick="window.open(<?= e(js('/' . setting('blog_url') . '/tag/' . $tag['slug'])) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?></button>
            <?php endif ?>
            <button type="submit" <?php if (!\Aurora\App\Permission::can('edit_tags')): ?> disabled <?php endif ?>><?= t('save') ?></button>
        </div>
    </div>
    <div class="grid small-form">
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
                <div class="extra-data">
                    <span><?= t('id') ?>: <?= e($tag['id']) ?></span>
                    <span><?= t('number_posts') ?>: <?= e($tag['posts']) ?></span>
                </div>
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
        <input type="hidden" name="csrf" value="<?= e($this->csrfToken()) ?>"/>
    </div>
</form>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('extra') ?>
    <script>
        window.id = <?= js($tag['id'] ?? '') ?>;

        document.getElementById('tag-form').addEventListener('submit', event => {
            event.preventDefault();
            updateSlug();
            Form.send('/admin/tags/save?id=' + window.id, 'tag-form', event.target.querySelector('[type="submit"]')).then(res => {
                if (typeof res?.id !== 'undefined') {
                    window.id = res.id;
                }
            });
        });

        function remove(btn) {
            if (!confirm(LANG.delete_confirm.sprintf(<?= js($tag['name'] ?? '') ?>))) {
                return;
            }

            Form.send('/admin/tags/remove', null, btn, {
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
            Form.initCharCounters();
        });
    </script>
<?php $this->sectionEnd() ?>
