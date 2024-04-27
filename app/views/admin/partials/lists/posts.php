<?php foreach ($posts as $post): ?>
    <a data-id="<?= e($post['id']) ?>" href="/admin/posts/edit?id=<?= e($post['id']) ?>" class="listing-row post" onclick="Listing.toggleRow(this, event)">
        <div class="w100 align-center">
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
        <?php if (setting('views_count')): ?>
            <div class="w10 numeric">
                <?= e($post['views'] ? $post['views'] : '') ?>
            </div>
        <?php endif ?>
        <div class="w10 row-actions">
            <div class="three-dots" onclick="return false" dropdown>
                <?= $this->include('icons/dots.svg') ?>
                <div class="dropdown-menu">
                    <div onclick="window.open(<?= e(js('/' . setting('blog_url') . '/' . $post['slug'])) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?> <?= t('view') ?></div>
                    <?php if (\Aurora\App\Permission::can('edit_posts')): ?>
                        <div
                            class="danger"
                            onclick="
                                if (confirm(LANG.delete_confirm.sprintf(<?= e(js($post['title'])) ?>))) {
                                    Form.send('/admin/posts/remove', null, null, {
                                        csrf: <?= e(js($this->csrfToken())) ?>,
                                        id: <?= e(js($post['id'])) ?>,
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
