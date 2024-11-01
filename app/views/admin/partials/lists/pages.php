<?php foreach ($pages as $page): ?>
    <a data-id="<?= e($page['id']) ?>" href="/admin/pages/edit?id=<?= e($page['id']) ?>" class="listing-row" onclick="Listing.toggleRow(this, event)">
        <div class="w100">
            <h3>
                <?= e($page['title']) ?>
                <?php if (!$page['status']): ?>
                    <span class="title-label red"><?= t('draft') ?></span>
                <?php endif ?>
            </h3>
        </div>
        <div class="w20">
            /<?= e($page['slug']) ?>
        </div>
        <div class="w20">
            <?= $this->dateFormat($page['edited_at']) ?>
        </div>
        <?php if (setting('views_count')): ?>
            <div class="w10 numeric">
                <?= e($page['views']) ?>
            </div>
        <?php endif ?>
        <div class="w10 row-actions">
            <div class="three-dots" onclick="return false" dropdown>
                <?= $this->include('icons/dots.svg') ?>
                <div class="dropdown-menu">
                    <div onclick="window.open(<?= e(js($this->url($page['slug']))) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?> <?= t('view') ?></div>
                    <?php if (\Aurora\App\Permission::can('edit_pages')): ?>
                        <div
                            class="danger"
                            onclick="confirm(LANG.delete_confirm.sprintf(<?= e(js($page['title'])) ?>)) && Form.send('/admin/pages/remove', null, null, {
                                    csrf: csrf_token,
                                    id: <?= e(js($page['id'])) ?>,
                                }).then(res => Listing.handleResponse(res));"
                        ><?= $this->include('icons/trash.svg') ?> <?= t('delete') ?></div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </a>
<?php endforeach ?>
