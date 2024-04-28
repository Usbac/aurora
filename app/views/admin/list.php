<?php $this->extend('admin/base.php') ?>

<?php $this->sectionStart('title') ?>
    <?= e("$title - " . setting('title')) ?>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('content') ?>
    <?php $current_path = '/' . \Aurora\System\Helper::getCurrentPath(); ?>
    <div>
        <?php if (isset($custom_header)): ?>
            <?= $custom_header ?>
        <?php else: ?>
            <div class="page-title">
                <?= $this->include('admin/partials/menu_btn.php') ?>
                <div>
                    <h2><?= e($title) ?></h2>
                    <span id="total-items">&nbsp;</span>
                    <span id="selected-items"></span>
                </div>
            </div>
            <?php if (!empty($show_add_button)): ?>
                <a href="<?= e("$current_path/edit") ?>" class="button" title="<?= t('new') ?>"><b>+</b>&nbsp;<?= t('new') ?></a>
            <?php endif ?>
        <?php endif ?>
    </div>
    <form id="filters-form" class="filters" target="<?= e($current_path) ?>" method="get">
        <?php foreach ($filters as $key => $filter): ?>
            <div class="input-group">
                <label><?= e($filter['title']) ?></label>
                <select name="<?= e($key) ?>" onchange="this.form.dispatchEvent(new CustomEvent('submit'))">
                    <?php foreach ($filter['options'] as $opt_value => $opt_title): ?>
                        <option value="<?= e($opt_value) ?>" <?php if (strval($_GET[$key] ?? '') === strval($opt_value)): ?> selected <?php endif ?>>
                            <?= e($opt_title) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>
        <?php endforeach ?>
        <input type="text" name="search" placeholder="<?= t('search') ?>" value="<?= e($_GET['search'] ?? '') ?>"/>
        <button type="submit"><?= $this->include('icons/glass.svg') ?></button>
    </form>
    <?php if (!empty($extra_header)): ?>
        <?= $this->include($extra_header) ?>
    <?php endif ?>
    <div class="listing-container">
        <div class="listing">
            <div class="listing-row header">
                <?php foreach (array_filter($columns, fn($column) => !isset($column['condition']) || $column['condition']) as $column): ?>
                    <div class="<?= e($column['class'] ?? '') ?>" title="<?= e($column['title']) ?>"><?= e($column['title']) ?></div>
                <?php endforeach ?>
            </div>
        </div>
        <div id="main-listing" class="listing"></div>
    </div>
    <button class="load-more hidden" onclick="Listing.loadNextPage()"><?= t('load_more') ?></button>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('extra') ?>
    <script>
        let filters_form = document.getElementById('filters-form');

        filters_form.addEventListener('submit', e => {
            e.preventDefault();
            let params = new URLSearchParams(new FormData(filters_form));
            Array.from(params).forEach(([ key, value ]) => {
                if (value === '') {
                    params.delete(key);
                }
            });

            window.history.replaceState('', '', '?' + params.toString());
            Listing.handleResponse({ success: true });
        });

        window.addEventListener('load', () => {
            Listing.init();
            Listing.setNextPageUrl(<?= js("$current_path/page") ?>);
            Listing.loadNextPage();
        });
    </script>
<?php $this->sectionEnd() ?>
