<?php $this->extend('admin/base.php') ?>

<?php $this->sectionStart('title') ?>
    <?= t('settings') ?>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('content') ?>
    <?php $allow_edit_settings = \Aurora\App\Permission::can('edit_settings'); ?>
    <form id="settings-form" class="content">
        <div>
            <div class="page-title">
                <?= $this->include('admin/partials/menu_btn.php') ?>
                <h2><?= t('settings') ?></h2>
            </div>
            <div class="buttons">
                <button type="submit" <?php if (!$allow_edit_settings): ?> disabled <?php endif ?>><?= t('save') ?></button>
            </div>
        </div>
        <div class="grid grid-two-columns wide">
            <div class="grid">
                <div>
                    <div class="tabs">
                        <a href="#general"><?= $this->include('icons/settings.svg') ?> <?= t('general') ?></a>
                        <a href="#meta"><?= $this->include('icons/note.svg') ?> <?= t('meta') ?></a>
                        <a href="#data"><?= $this->include('icons/database.svg') ?> <?= t('data') ?></a>
                        <a href="#advanced"><?= $this->include('icons/terminal.svg') ?> <?= t('advanced') ?></a>
                        <a href="#info"><?= $this->include('icons/server.svg') ?> <?= t('server_info') ?></a>
                        <a href="#code"><?= $this->include('icons/code.svg') ?> <?= t('code') ?></a>
                        <a href="#update"><?= $this->include('icons/sync.svg') ?> <?= t('update') ?></a>
                    </div>
                    <p class="version"><?= t('version') ?>: <?= e(\Aurora\Core\Kernel::VERSION) ?></p>
                </div>
            </div>
            <div class="grid tab-content" data-tab="#general">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label><?= t('logo') ?></label>
                        <img src="<?= e(!empty(setting('logo')) ? $this->getContentUrl(setting('logo')) : '/public/assets/no-image.svg') ?>" class="logo pointer <?php if (empty(setting('logo'))): ?>empty-img<?php endif ?>" alt="logo"/>
                        <input id="settings-logo-input" type="hidden" name="logo" value="<?= e(setting('logo')) ?>"/>
                    </div>
                    <div class="input-group-container">
                        <div class="input-group">
                            <label for="title"><?= t('title') ?></label>
                            <input id="title" name="title" type="text" value="<?= e(setting('title')) ?>" data-char-count/>
                        </div>
                    </div>
                    <div class="input-group-container">
                        <div class="input-group">
                            <label for="blog-url"><?= t('blog_url') ?></label>
                            <input id="blog-url" name="blog_url" type="text" placeholder="/blog" value="<?= e(setting('blog_url')) ?>"/>
                        </div>
                        <div class="input-group">
                            <label for="rss"><?= t('rss_url', false) ?></label>
                            <input id="rss" name="rss" type="text" placeholder="/rss" value="<?= e(setting('rss')) ?>"/>
                        </div>
                    </div>
                    <div class="input-group-container">
                        <div class="input-group">
                            <label><?= t('theme') ?></label>
                            <select name="theme">
                                <?php foreach ($themes as $theme): ?>
                                    <option value="<?= e($theme) ?>" <?php if (setting('theme') == $theme): ?> selected <?php endif ?>><?= e($theme) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="per_page"><?= t('items_per_page') ?></label>
                            <input id="per_page" name="per_page" type="number" placeholder="20" min="1" value="<?= e(setting('per_page')) ?>"/>
                        </div>
                    </div>
                    <div class="input-group-container">
                        <div class="input-group">
                            <label><?= t('system_language') ?></label>
                            <span class="description"><?= t('system_language_description', false) ?></span>
                            <select name="language">
                                <?php foreach ($languages as $lang): ?>
                                    <option value="<?= e($lang) ?>" <?php if (setting('language') == $lang): ?> selected <?php endif ?>><?= e($lang) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="date_format"><?= t('date_format') ?></label>
                            <span class="description"><?= t('date_format_description', false) ?></span>
                            <input id="date_format" name="date_format" type="text" placeholder="MMM d, Y" value="<?= e(setting('date_format')) ?>"/>
                        </div>
                    </div>
                    <div class="input-group-container">
                        <div class="input-group">
                            <label><?= t('maintenance_mode') ?></label>
                            <div class="switch">
                                <input id="maintenance" name="maintenance" type="checkbox" <?php if (setting('maintenance')): ?> checked <?php endif ?>>
                                <button type="button" class="slider" onclick="get('#maintenance').click()"></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid tab-content" data-tab="#meta">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label for="meta_title"><?= t('meta_title') ?></label>
                        <input id="meta_title" name="meta_title" type="text" value="<?= e(setting('meta_title')) ?>" data-char-count/>
                    </div>
                    <div class="input-group">
                        <label for="description"><?= t('description') ?></label>
                        <textarea id="description" name="description" data-char-count><?= e(setting('description')) ?></textarea>
                    </div>
                    <div class="input-group">
                        <label for="meta_description"><?= t('meta_description') ?></label>
                        <textarea id="meta_description" name="meta_description" data-char-count><?= e(setting('meta_description')) ?></textarea>
                    </div>
                    <div class="input-group">
                        <label for="meta_keywords"><?= t('meta_keywords') ?></label>
                        <input id="meta_keywords" name="meta_keywords" type="text" value="<?= e(setting('meta_keywords')) ?>"/>
                    </div>
                </div>
            </div>
            <div class="grid tab-content" data-tab="#advanced">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label for="session_lifetime"><?= t('session_lifetime') ?></label>
                        <span class="description"><?= t('session_lifetime_description') ?></span>
                        <input id="session_lifetime" name="session_lifetime" type="number" value="<?= e(setting('session_lifetime')) ?>"/>
                    </div>
                    <div class="input-group">
                        <label for="samesite_cookie"><?= t('samesite_cookie') ?></label>
                        <span class="description"><?= t('samesite_cookie_description') ?></span>
                        <select name="samesite_cookie">
                            <?php foreach ([ 'None', 'Lax', 'Strict' ] as $cookie): ?>
                                <option value="<?= e($cookie) ?>" <?php if (setting('samesite_cookie') == $cookie): ?> selected <?php endif ?>><?= e($cookie) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="input-group-container">
                        <div class="input-group">
                            <label><?= t('log_errors') ?></label>
                            <div class="switch">
                                <input id="log_errors" name="log_errors" type="checkbox" <?php if (setting('log_errors')): ?> checked <?php endif ?>>
                                <button type="button" class="slider" onclick="get('#log_errors').click()"></button>
                            </div>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="log_file"><?= t('log_file') ?></label>
                        <span class="description"><?= t('relative_system_root') ?></span>
                        <input id="log_file" name="log_file" type="text" value="<?= e(setting('log_file')) ?>"/>
                    </div>
                </div>
                <?php if (!empty(setting('log_file'))): ?>
                    <div class="card v-spacing">
                        <div id="logs" class="input-group">
                            <label><?= t('logs') ?></label>
                            <?php $log_file = \Aurora\Core\Helper::getPath(setting('log_file')) ?>
                            <textarea placeholder="<?= t('no_logs') ?>" readonly><?= e(file_exists($log_file) ? file_get_contents($log_file) : '') ?></textarea>
                            <div class="input-group">
                                <input type="hidden" name="csrf" value="<?= e($this->csrfToken()) ?>"/>
                                <button type="button" class="light" onclick="location = '/admin/settings/logs_download'"><?= t('download') ?></button>
                                <button type="button" class="delete" onclick="confirm(LANG.logs_clear_confirm) && Form.send('/admin/settings/logs_clear', 'logs')" <?php if (!$allow_edit_settings): ?> disabled <?php endif ?>><?= t('clear') ?></button>
                            </div>
                        </div>
                    </div>
                <?php endif ?>
            </div>
            <div class="grid tab-content" data-tab="#data">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label><?= t('download_db') ?></label>
                        <button type="button" class="light" onclick="location = '/admin/settings/db'" <?php if (!$allow_edit_settings): ?> disabled <?php endif ?>>.json</button>
                    </div>
                    <div id="db-upload" class="input-group">
                        <label for="database"><?= t('upload_db') ?></label>
                        <div class="input-file">
                            <input id="database" type="file" name="db" class="hidden"/>
                            <input type="text" disabled/>
                            <label for="database" class="pointer"><?= t('select_file') ?></label>
                        </div>
                        <button type="button" class="light" onclick="confirm(LANG.upload_db_confirm) && Form.send('/admin/settings/db_upload', 'db-upload', null, { csrf: csrf_token })" <?php if (!$allow_edit_settings): ?> disabled <?php endif ?>><?= t('upload') ?> .json</button>
                    </div>
                    <div class="input-group">
                        <label><?= t('views_counter') ?></label>
                        <div class="switch">
                            <input id="views-count" name="views_count" type="checkbox" <?php if (setting('views_count')): ?> checked <?php endif ?>>
                            <button type="button" class="slider" onclick="get('#views-count').click()"></button>
                        </div>
                        <div id="reset-views">
                            <button type="button" class="light" onclick="confirm(LANG.reset_views_confirm) && Form.send('/admin/settings/reset_views_count', 'reset-views')" <?php if (!$allow_edit_settings): ?> disabled <?php endif ?>><?= t('reset_views') ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid tab-content" data-tab="#info">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label><?= t('operating_system') ?></label>
                        <span><?= e(php_uname('s') . ' ' . php_uname('r')) ?></span>
                    </div>
                    <div class="input-group">
                        <label><?= t('php_version') ?></label>
                        <span><?= e(phpversion()) ?></span>
                    </div>
                    <div class="input-group">
                        <label><?= t('db') ?></label>
                        <span><?= e($db_dsn) ?></span>
                    </div>
                    <div class="input-group">
                        <label><?= t('host_name') ?></label>
                        <span><?= e(gethostname()) ?></span>
                    </div>
                    <div class="input-group">
                        <label><?= t('root_folder') ?></label>
                        <span><?= e(rtrim(\Aurora\Core\Helper::getPath(), '/')) ?></span>
                    </div>
                    <div class="input-group">
                        <label><?= t('memory_limit') ?></label>
                        <span><?= e(\Aurora\Core\Helper::getByteSize(\Aurora\Core\Helper::getPhpSize(ini_get('memory_limit')))) ?></span>
                    </div>
                    <div class="input-group">
                        <label><?= t('file_size_upload_limit') ?></label>
                        <span class="description"><?= t('file_size_upload_limit_description', false) ?></span>
                        <span><?= e(\Aurora\Core\Helper::getByteSize(\Aurora\App\Media::getMaxUploadFileSize())) ?></span>
                    </div>
                </div>
            </div>
            <div class="grid tab-content" data-tab="#code">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label for="site-header"><?= t('site_header') ?></label>
                        <span class="description"><?= t('site_header_description', false) ?></span>
                        <textarea id="site-header" name="header_code" class="code"><?= e(setting('header_code') ?? '') ?></textarea>
                    </div>
                    <div class="input-group">
                        <label for="site-footer"><?= t('site_footer') ?></label>
                        <span class="description"><?= t('site_footer_description', false) ?></span>
                        <textarea id="site-footer" name="footer_code" class="code"><?= e(setting('footer_code') ?? '') ?></textarea>
                    </div>
                    <div class="input-group">
                        <label for="post-code"><?= t('post_code') ?></label>
                        <span class="description"><?= t('post_code_description', false) ?></span>
                        <textarea id="post-code" name="post_code" class="code"><?= e(setting('post_code') ?? '') ?></textarea>
                    </div>
                    <div class="input-group">
                        <label for="editor-code"><?= t('editor_code') ?></label>
                        <span class="description"><?= t('editor_code_description', false) ?></span>
                        <textarea id="editor-code" name="editor_code" class="code"><?= e(setting('editor_code') ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            <div class="grid tab-content" data-tab="#update">
                <div class="card v-spacing">
                    <div id="update" class="input-group">
                        <label id="update-title" for="update-button"></label>
                        <span class="description"><?= t('update_description', false) ?></span>
                        <button type="button" id="update-button" class="light" onclick="update()" <?php if (!\Aurora\App\Permission::can('update')): ?> disabled <?php endif ?>><?= t('update_now') ?></button>
                        <input id="update-zip" type="hidden" name="zip"/>
                    </div>
                </div>
            </div>
            <input type="hidden" name="csrf" value="<?= e($this->csrfToken()) ?>"/>
        </div>
        <div id="image-dialog" class="dialog image-dialog">
            <div></div>
        </div>
    </form>
<?php $this->sectionEnd() ?>

<?php $this->sectionStart('extra') ?>
    <script>
        var new_version = '';
        var update_handler_initialized = false;

        document.getElementById('settings-form').addEventListener('submit', event => {
            event.preventDefault();
            Form.send('/admin/settings/save', 'settings-form', event.target.querySelector('[type="submit"]'));
        });

        function initUpdateHandler() {
            if (update_handler_initialized) {
                return;
            }

            update_handler_initialized = true;
            let update_title = get('#update-title');
            let update_button = get('#update-button');
            update_title.innerHTML = LANG.update_check;
            update_button.setLoading();

            fetch('/admin/settings/update_version')
                .then(res => res.json())
                .then(res => {
                    update_button.resetState();
                    if (res === false) {
                        update_title.innerHTML = LANG.update_not_found;
                        update_button.setAttribute('disabled', true);
                    } else if (typeof res === 'object' && res !== null) {
                        update_title.innerHTML = LANG.update_found.sprintf(res.version);
                        new_version = res.version;
                        get('#update-zip').value = res.zip;
                    } else {
                        update_title.innerHTML = LANG.update_check_error;
                        update_button.onclick = () => location.reload();
                        update_button.innerHTML = LANG.try_again;
                    }
                })
                .catch(() => alert(LANG.unexpected_error));
        }

        function update() {
            return confirm(LANG.update_confirm.sprintf(new_version)) && Form.send('/admin/settings/update', 'update', null, {
                csrf: csrf_token,
            }).then(res => {
                if (res.success) {
                    setTimeout(() => location.reload(), 2000);
                }
            });
        }

        addEventListener('hashchange', () => {
            if (!location.hash) {
                return;
            }

            if (location.hash == '#update') {
                initUpdateHandler();
            }

            document.querySelectorAll('.tab-content').forEach(el => el.style.display = el.dataset.tab == location.hash
                ? 'grid'
                : 'none');
            document.querySelectorAll('.tabs > a').forEach(el => el.getAttribute('href') == location.hash
                ? el.dataset.checked = true
                : delete el.dataset.checked);
        });

        window.addEventListener('load', () => {
            ImageDialog.init(get('#image-dialog'), get('#settings-logo-input'), get('img.logo'), <?= js(\Aurora\Core\Kernel::config('content')) ?>);
            Form.initCharCounters();
            Form.initFileInput(document.querySelector('#db-upload > .input-file'));

            if (!location.hash.substring(1)) {
                location.hash = '#general';
            }

            window.dispatchEvent(new HashChangeEvent('hashchange'));
        });
    </script>
<?php $this->sectionEnd() ?>
