<footer>
    <div>
        <img class="logo pointer" src="<?= e($this->getContentUrl(setting('logo'))) ?>" alt="<?= e(setting('title')) ?>" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"/>
        <span><?= t('made_with') ?></span>
    </div>
</footer>
<?= setting('footer_code') ?? '' ?>
