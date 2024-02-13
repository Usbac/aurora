<?php foreach ($tags as $tag): ?>
    <a href="/admin/tags/edit?id=<?= e($tag['id']) ?>" class="listing-row tag">
        <div class="w50">
            <h3><?= e($tag['name']) ?></h3>
        </div>
        <div class="w30">
            <?= e($tag['slug']) ?>
        </div>
        <div class="w10 numeric">
            <?= e($tag['posts']) ?>
        </div>
        <div class="w10 row-actions">
            <div class="three-dots" onclick="return false" dropdown>
                <?= $this->include('icons/dots.svg') ?>
                <div class="dropdown-menu">
                    <div onclick="window.open(<?= e(js('/' . setting('blog_url') . '/tag/' . $tag['slug'])) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?> <?= t('view') ?></div>
                    <?php if (\Aurora\App\Permission::can('edit_tags')): ?>
                        <div
                            class="danger"
                            onclick="
                                if (confirm(<?= e(js(t('delete_confirm', false))) ?>.sprintf(<?= e(js($tag['name'])) ?>))) {
                                    Form.send('/admin/tags/remove/' + <?= e(js($tag['id'])) ?>, null, null, {
                                        csrf: <?= e(js($this->csrfToken())) ?>,
                                    }).then(res => {
                                        if (res.success) {
                                            Dropdown.close();
                                            Listing.setNextPage(1);
                                            Listing.loadNextPage();
                                        }
                                    });
                                }
                            "
                        ><?= $this->include('icons/trash.svg') ?> <?= t('delete') ?></div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </a>
<?php endforeach ?>
