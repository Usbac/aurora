<?php foreach ($tags as $tag): ?>
    <a data-id="<?= e($tag['id']) ?>" href="/admin/tags/edit?id=<?= e($tag['id']) ?>" class="listing-row tag" onclick="Listing.toggleRow(this, event)">
        <div class="w100">
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
                            onclick="confirm(LANG.delete_confirm.sprintf(<?= e(js($tag['name'])) ?>)) && Form.send('/admin/tags/remove', null, null, {
                                    csrf: csrf_token,
                                    id: <?= e(js($tag['id'])) ?>,
                                }).then(res => Listing.handleResponse(res));"
                        ><?= $this->include('icons/trash.svg') ?> <?= t('delete') ?></div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </a>
<?php endforeach ?>
