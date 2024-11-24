<?php foreach ($users as $user): ?>
    <a data-id="<?= e($user['id']) ?>" href="/admin/users/edit?id=<?= e($user['id']) ?>" class="listing-row user" onclick="Listing.toggleRow(this, event)">
        <div class="w100 align-center">
            <div class="user-image">
                <img src="<?= e($this->getContentUrl($user['image'] ?? '')) ?>" alt="<?= e($user['name'] ?? '') ?>" <?php if (empty($user['image'])): ?> style="visibility: hidden;" <?php endif ?>/>
            </div>
            <div>
                <h3>
                    <?= e($user['name']) ?>
                    <?php if ($user['id'] == $_SESSION['user']['id']): ?>
                        <span class="you-tag">(<?= t('you') ?>)</span>
                    <?php endif ?>
                    <?php if ($user['status'] != 1): ?>
                        <span class="title-label red"><?= t('inactive') ?></span>
                    <?php endif ?>
                </h3>
                <p class="subtitle"><?= e($user['email']) ?></p>
            </div>
        </div>
        <div class="w20">
            <?= t($user['role_slug']) ?>
        </div>
        <div class="w20">
            <?= e($this->dateFormat($user['last_active'])) ?>
        </div>
        <div class="w10 numeric">
            <?= e($user['posts']) ?>
        </div>
        <div class="w10 row-actions">
            <div class="three-dots" onclick="return false" dropdown>
                <?= $this->include('icons/dots.svg') ?>
                <div class="dropdown-menu">
                    <div onclick="window.open(<?= e(js('/' . setting('blog_url') . '/author/' . $user['slug'])) ?>, '_blank').focus()"><?= $this->include('icons/eye.svg') ?> <?= t('view') ?></div>
                    <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                        <?php if (\Aurora\App\Permission::impersonate($user)): ?>
                            <div onclick="if (confirm(LANG.impersonate_confirm)) location.href = '/admin/users/impersonate?id=' + <?= e(js($user['id'])) ?>"><?= $this->include('icons/users.svg') ?> <?= t('impersonate') ?></div>
                        <?php endif ?>
                        <?php if (\Aurora\App\Permission::edit_user($user)): ?>
                            <div
                                class="danger"
                                onclick="confirm(LANG.delete_confirm.sprintf(<?= e(js($user['name'])) ?>)) && Form.send('/admin/users/remove', null, null, {
                                        csrf: csrf_token,
                                        id: <?= e(js($user['id'])) ?>,
                                    }).then(res => Listing.handleResponse(res));"
                            ><?= $this->include('icons/trash.svg') ?> <?= t('delete') ?></div>
                        <?php endif ?>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </a>
<?php endforeach ?>
