<rss version="2.0">
    <channel>
        <title><?= e(setting('title')) ?></title>
        <link><?= e($this->url()) ?></link>
        <description><?= e(setting('description')) ?></description>
        <language><?= e(\Aurora\Core\Container::get('language')->getCode()) ?></language>
        <?php if (setting('logo')): ?>
            <image>
                <url><?= e($this->getContentUrl(setting('logo'))) ?></url>
                <title><?= e(setting('title')) ?></title>
                <link><?= e($this->url()) ?></link>
            </image>
        <?php endif ?>
        <?php foreach ($posts as $post): ?>
            <item>
                <title><?= e($post['title']) ?></title>
                <link><?= e($this->url('/' . setting('blog_url') . '/' . $post['slug'])) ?></link>
                <description><?= e($post['description']) ?></description>
                <?php if ($post['user_id']): ?>
                    <author><?= e($post['user_email'] . ' (' . $post['user_name'] . ')') ?></author>
                <?php endif ?>
                <?php foreach ($post['tags'] as $tag): ?>
                    <category><?= e($tag) ?></category>
                <?php endforeach ?>
                <pubDate><?= date('r', $post['published_at']) ?></pubDate>
                <guid><?= e($this->url('/' . setting('blog_url') . '/' . $post['slug'])) ?></guid>
            </item>
        <?php endforeach ?>
    </channel>
</rss>
