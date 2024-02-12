<!DOCTYPE html>
<html lang="<?= e($this->lang()) ?>">
<head>
    <title><?= t('post') . ' - ' . e(setting('title')) ?></title>
    <?= $this->include('admin/partials/head.php') ?>
</head>
<body class="admin">
    <?= $this->include('admin/partials/nav.php') ?>
    <div class="content">
        <?php $can_edit_post = \Aurora\App\Permission::can('edit_posts'); ?>
        <div>
            <div class="page-title">
                <?= $this->include('admin/partials/menu_btn.php') ?>
                <h2><?= t('post') ?></h2>
            </div>
            <div class="buttons">
                <?php if (\Aurora\System\Helper::isValidId($post['id'] ?? false)): ?>
                    <button class="delete" onclick="remove(this);" <?php if (!$can_edit_post): ?> disabled <?php endif ?>>
                        <?= $this->include('icons/trash.svg') ?>
                    </button>
                    <button onclick="window.open(<?= e(js('/' . setting('blog_url') . '/' . $post['slug'])) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?></button>
                <?php endif ?>
                <button id="save" onclick="save();" <?php if (!$can_edit_post): ?> disabled <?php endif ?>><?= t('save') ?></button>
            </div>
        </div>
        <div id="post-form" class="grid grid-two-columns">
            <div class="grid">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label for="title"><?= t('title') ?></label>
                        <input id="title" type="text" name="title" value="<?= e($post['title'] ?? '') ?>" char-count/>
                    </div>
                    <?php if (!empty($post['image'])): ?>
                        <img src="<?= e($this->getContentUrl($post['image'])) ?>" class="pointer post-image"/>
                    <?php else: ?>
                        <img src="/public/assets/no-image.svg" class="pointer post-image empty-img"/>
                    <?php endif ?>
                    <input id="post-image-input" type="hidden" name="image" value="<?= e($post['image'] ?? '') ?>"/>
                </div>
                <textarea id="html" name="html"><?= $post['html'] ?? '' ?></textarea>
            </div>
            <div class="grid">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label for="slug"><?= t('slug') ?></label>
                        <input id="slug" name="slug" type="text" placeholder="lorem-ipsum" value="<?= e($post['slug'] ?? '') ?>" maxlength="255" char-count/>
                        <a id="post-link" target="_blank"></a>
                    </div>
                    <div class="input-group">
                        <label for="description"><?= t('description') ?></label>
                        <textarea id="description" name="description" char-count><?= e($post['description'] ?? '') ?></textarea>
                    </div>
                    <?php if (\Aurora\System\Helper::isValidId($post['id'] ?? false)): ?>
                        <div class="extra-data">
                            <span><?= t('id') ?>: <?= e($post['id']) ?></span>
                            <span><?= t('number_views') ?>: <?= e($post['views']) ?></span>
                        </div>
                    <?php endif ?>
                </div>
                <div class="card v-spacing">
                    <div class="input-group">
                        <label for="published_at"><?= t('publish_date') ?></label>
                        <input id="published_at" name="published_at" type="date" value="<?= e(date('Y-m-d', $post['published_at'] ?? time())) ?>"/>
                    </div>
                    <div class="input-group">
                        <label for="user_id"><?= t('author') ?></label>
                        <select id="user_id" name="user_id">
                            <option value=""></option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e($user['id']) ?>" <?php if (\Aurora\System\Helper::isValidId($post['user_id'] ?? false) && $post['user_id'] == $user['id']): ?> selected <?php endif ?>><?= e($user['name']) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="published_at"><?= t('published') ?></label>
                        <div class="switch">
                            <input id="status" name="status" type="checkbox" <?php if ($post['status'] ?? false): ?> checked <?php endif ?>>
                            <button class="slider" onclick="get('#status').click()"></button>
                        </div>
                    </div>
                    <?php if (!empty($tags)): ?>
                        <div class="input-group">
                            <label><?= t('tags') ?></label>
                            <div class="checkbox">
                                <?php $post_tags = $post['tags'] ?? [] ?>
                                <?php foreach ($tags as $tag): ?>
                                    <label><input type="checkbox" multiselect name="tags" value="<?= e($tag['id']) ?>" <?php if (array_key_exists($tag['slug'], $post_tags)): ?> checked <?php endif ?>><?= e($tag['name']) ?></label>
                                <?php endforeach ?>
                            </div>
                        </div>
                    <?php endif ?>
                </div>
                <div class="card v-spacing">
                    <div class="input-group">
                        <label for="image-alt"><?= t('image_alt') ?></label>
                        <input id="image-alt" type="text" name="image_alt" value="<?= e($post['image_alt'] ?? '') ?>"/>
                    </div>
                    <div class="input-group">
                        <label for="meta-title"><?= t('meta_title') ?></label>
                        <input id="meta-title" name="meta_title" type="text" placeholder="lorem ipsum" value="<?= e($post['meta_title'] ?? '') ?>" char-count/>
                    </div>
                    <div class="input-group">
                        <label for="meta-description"><?= t('meta_description') ?></label>
                        <textarea id="meta-description" name="meta_description" char-count><?= e($post['meta_description'] ?? '') ?></textarea>
                    </div>
                    <div class="input-group">
                        <label for="canonical-url"><?= t('canonical_url') ?></label>
                        <input id="canonical-url" name="canonical_url" type="text" placeholder="<?= e($this->url(setting('blog_url') . '/lorem-ipsum')) ?>" value="<?= e($post['canonical_url'] ?? '') ?>"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <dialog id="image-dialog" class="image-dialog"></dialog>
</body>
</html>
<?= setting('editor_code') ?>
<script>
    window.id = <?= js($post['id'] ?? '') ?>;

    function save() {
        updateSlug();
        Form.send('/admin/posts/save?id=' + window.id, 'post-form').then(res => {
            updateUrl();
            if (typeof res?.id !== 'undefined') {
                window.id = res.id;
            }
        });
    }

    function remove(btn) {
        if (!confirm(<?= js(t('delete_confirm', false)) ?>.sprintf(<?= js($post['title'] ?? '') ?>))) {
            return;
        }

        Form.send('/admin/posts/remove/' + window.id, null, btn).then(res => {
            if (res.success) {
                setTimeout(() => history.back(), 2000);
            }
        });
    }

    function updateUrl() {
        let slug = get('#slug').value;
        if (!slug) {
            return;
        }

        let url = <?= js($this->url(setting('blog_url'))) ?> + '/' + slug;
        get('#post-link').innerHTML = url;
        get('#post-link').setAttribute('href', url);
    }

    function updateSlug() {
        let slug = get('#slug');
        if (!slug.value.trim()) {
            slug.value = get('#title').value.toSlug();
            slug.dispatchEvent(new Event('input'));
        }
    }

    window.addEventListener('load', () => {
        ImageDialog.init(get('#image-dialog'), get('#post-image-input'), get('img.post-image'), <?= js(\Aurora\System\Kernel::config('content')) ?>);
        Form.initCharCounters();
        updateUrl();
    });
</script>
