<?php foreach ($posts as $post): ?>
    <a href="/admin/posts/edit?id=<?= e($post['id']) ?>" class="listing-row post">
        <div class="w40 align-center">
            <img src="<?= e($this->getContentUrl($post['image'] ?? '')) ?>" alt="<?= e($post['image_alt'] ?? '') ?>" <?php if (empty($post['image'])): ?> style="visibility: hidden;" <?php endif ?>/>
            <div class="main-data">
                <h3>
                    <?= e($post['title']) ?>
                    <?php if (!$post['status']): ?>
                        <span class="title-label red"><?= t('draft') ?></span>
                    <?php elseif ($post['published_at'] > time()): ?>
                        <span class="title-label"><?= t('scheduled') ?></span>
                    <?php endif ?>
                </h3>
                <p class="subtitle"><?= e(implode(', ', $post['tags'])) ?></p>
            </div>
        </div>
        <div class="w20">
            <?= e($post['user_name'] ?? '') ?>
        </div>
        <div class="w20">
            <?= e($this->dateFormat($post['published_at'])) ?>
        </div>
        <div class="w10 numeric">
            <?= e($post['views'] ? $post['views'] : '') ?>
        </div>
        <div class="w10 row-actions">
            <div class="three-dots" onclick="return false" dropdown>
                <?= $this->include('icons/dots.svg') ?>
                <div class="dropdown-menu">
                    <div onclick="window.open(<?= e(js('/' . setting('blog_url') . '/' . $post['slug'])) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?> <?= t('view') ?></div>
                    <?php if (\Aurora\App\Permission::can('edit_posts')): ?>
                        <div
                            class="danger"
                            onclick="
                                if (confirm(<?= e(js(t('delete_confirm', false))) ?>.sprintf(<?= e(js($post['title'])) ?>))) {
                                    Form.send('/admin/posts/remove/' + <?= e(js($post['id'])) ?>, null, null, {
                                        csrf: <?= e(js($this->csrfToken())) ?>,
                                    }).then(res => Listing.handleResponse(res));
                                }
                            "
                        ><?= $this->include('icons/trash.svg') ?> <?= t('delete') ?></div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </a>
<?php endforeach ?>
