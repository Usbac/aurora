<?php foreach ($links as $link): ?>
    <a href="/admin/links/edit?id=<?= e($link['id']) ?>" class="listing-row">
        <div class="w40">
            <h3><?= e($link['title']) ?></h3>
        </div>
        <div class="w20">
            <?= $link['url'] ?>
        </div>
        <div class="w20">
            <?php if ($link['status'] == 1): ?>
                <span class="title-label green"><?= t('active') ?></span>
            <?php else: ?>
                <span class="title-label red"><?= t('inactive') ?></span>
            <?php endif ?>
        </div>
        <div class="w10 numeric">
            <?= $link['order'] ?>
        </div>
        <div class="w10 row-actions">
            <div class="three-dots" onclick="return false" dropdown>
                <?= $this->include('icons/dots.svg') ?>
                <div class="dropdown-menu">
                    <div onclick="window.open(<?= e(js($link['url'])) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?> <?= t('view') ?></div>
                    <?php if (\Aurora\App\Permission::can('edit_links')): ?>
                        <div
                            class="danger"
                            onclick="
                                if (confirm(<?= e(js(t('delete_confirm', false))) ?>.sprintf(<?= e(js($link['title'])) ?>)))
                                    Form.send('/admin/links/remove/' + <?= e(js($link['id'])) ?>)
                                        .then(res => {
                                            if (res.success) {
                                                Dropdown.close();
                                                Listing.setNextPage(1);
                                                Listing.loadNextPage();
                                            }
                                        });
                            "
                        ><?= $this->include('icons/trash.svg') ?> <?= t('delete') ?></div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </a>
<?php endforeach ?>
