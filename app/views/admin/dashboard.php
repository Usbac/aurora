<?php $this->extend('admin/base.php') ?>

<?php $this->sectionStart('title') ?>
    <?= t('dashboard') ?>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('content') ?>
    <div class="content">
        <div>
            <div class="page-title">
                <?= $this->include('admin/partials/menu_btn.php') ?>
                <h2><?= t('dashboard') ?></h2>
            </div>
        </div>
        <div class="grid">
            <div class="grid grid-two-columns">
                <div class="grid">
                    <?php if (!empty($links)): ?>
                        <div class="card dashboard v-spacing">
                            <h3><?= t('links') ?></h3>
                            <div class="dashboard-card-rows links">
                                <?php foreach ($links as $link): ?>
                                    <a href="<?= e($link['url']) ?>" target="_blank">
                                        <?= e($link['title']) ?>
                                    </a>
                                <?php endforeach ?>
                            </div>
                        </div>
                    <?php endif ?>
                    <div class="card dashboard v-spacing">
                        <h3><?= t('latest_published_posts') ?></h3>
                        <div class="dashboard-card-rows">
                            <?php foreach ($posts as $post): ?>
                                <a href="<?= e('/' . setting('blog_url') . '/' . $post['slug']) ?>" target="_blank">
                                    <img src="<?= e($this->getContentUrl($post['image'] ?? '')) ?>" alt="<?= e($post['title']) ?>" <?php if (empty($post['image'])): ?> style="visibility: hidden;" <?php endif ?>/>
                                    <div>
                                        <b><?= e($post['title']) ?></b>
                                        <span class="subtitle">
                                            <?php if ($post['user_id']): ?>
                                                <?= t('by') . ' ' . e($post['user_name']) ?>
                                            <?php else: ?>
                                                &nbsp;
                                            <?php endif ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach ?>
                            <?php if (empty($posts)): ?>
                                <span class="empty"><?= t('no_results') ?></span>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
                <div class="grid">
                    <div class="card dashboard v-spacing">
                        <h3><?= t('start_creating') ?></h3>
                        <div class="start-creating">
                            <a href="/admin/pages/edit"><?= $this->include('icons/book.svg') ?> <span><?= t('create_page') ?></span></a>
                            <a href="/admin/posts/edit"><?= $this->include('icons/pencil.svg') ?> <span><?= t('write_post') ?></span></a>
                            <a href="/admin/users/edit"><?= $this->include('icons/user.svg') ?> <span><?= t('add_user') ?></span></a>
                            <a href="/admin/tags/edit"><?= $this->include('icons/tag.svg') ?> <span><?= t('add_tag') ?></span></a>
                        </div>
                    </div>
                    <div class="card dashboard v-spacing">
                        <h3><?= t('statistics') ?></h3>
                        <div>
                            <div class="input-group">
                                <b><?= t('posts') ?></b>
                                <span>
                                    <?= e($total_posts) ?> <?= t('published') ?>,
                                    <?= e($total_scheduled_posts) ?> <?= t('scheduled') ?>,
                                    <?= e($total_draft_posts) ?> <?= t('draft') ?>
                                </span>
                            </div>
                            <div class="input-group">
                                <b><?= t('pages') ?></b>
                                <span>
                                    <?= e($total_pages) ?> <?= t('published') ?>,
                                    <?= e($total_draft_pages) ?> <?= t('draft') ?>
                                </span>
                            </div>
                            <div class="input-group">
                                <b><?= t('users') ?></b>
                                <span>
                                    <?= e($total_users) ?> <?= t('active') ?>,
                                    <?= e($total_inactive_users) ?> <?= t('inactive') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $this->sectionEnd() ?>
