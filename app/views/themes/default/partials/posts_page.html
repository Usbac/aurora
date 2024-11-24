<?php foreach ($posts as $post): ?>
    <div class="post-card">
        <a href="<?= e('/' . setting('blog_url') . '/' . $post['slug']) ?>" class="image pointer">
            <img src="<?= e($this->getContentUrl($post['image'] ?? '')) ?>" alt="<?= e($post['image_alt'] ?? '') ?>" <?php if (empty($post['image'])): ?> style="visibility: hidden" <?php endif ?>/>
        </a>
        <div>
            <?php if (!$post['status']): ?>
                <span class="label red"><?= t('draft') ?></span>
            <?php elseif ($post['published_at'] > time()): ?>
                <span class="label"><?= t('scheduled') ?></span>
            <?php endif ?>
            <?php if (!empty($post['tags'])): ?>
                <div class="post-tags">
                    <?php foreach ($post['tags'] as $tag_slug => $tag_title): ?>
                        <a href="<?= e('/' . setting('blog_url') . '/tag/' . $tag_slug) ?>"><?= e($tag_title) ?></a>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
            <h2>
                <a href="<?= e('/' . setting('blog_url') . '/' . $post['slug']) ?>"><?= e($post['title']) ?></a>
            </h2>
            <div class="meta">
                <?php if ($post['user_id']): ?>
                    <div>
                        <?php $user_url = '/' . setting('blog_url') . '/author/' . $post['user_slug'] ?>
                        <a href="<?= e($user_url) ?>" class="pointer">
                            <img src="<?= !empty($post['user_image']) ? e($this->getContentUrl($post['user_image'])) : '/public/assets/user.svg' ?>" alt="<?= t('author') ?>"/>
                        </a>
                        <a href="<?= e($user_url) ?>"><?= e($post['user_name']) ?></a>
                    </div>
                <?php endif ?>
                <span><?= e($this->dateFormat($post['published_at'], setting('date_format'))) ?></span>
                <span><?= e($this->getReadTime($post['html'])) ?> <?= t('minutes_read') ?></span>
            </div>
            <div class="description"><?= e($post['description']) ?></div>
        </div>
    </div>
<?php endforeach ?>
