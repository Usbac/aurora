<?php $can_edit_media = \Aurora\App\Permission::can('edit_media'); ?>
<div class="page-title">
    <?= $this->include('admin/partials/menu_btn.php') ?>
    <div>
        <h2><?= t('media') ?></h2>
        <div>
            <div class="media-paths">
                <?php $paths = explode('/', trim($path, '/')) ?>
                <?php foreach ($paths as $i => $folder): ?>
                    <?php $folder_path = implode('/', array_slice($paths, 0, $i + 1)) ?>
                    <?php if (\Aurora\App\Media::isValidPath(\Aurora\System\Helper::getPath($folder_path))): ?>
                        <a href="/admin/media?path=<?= e($folder_path) ?>" class="pointer"><?= e($folder) ?></a>
                        <span>/</span>
                    <?php endif ?>
                <?php endforeach ?>
            </div>
            <span id="total-items">&nbsp;</span>
        </div>
    </div>
</div>
<div class="media-options">
    <button onclick="downloadAll()" title="<?= t('download_zip') ?>"><?= $this->include('icons/zip.svg') ?></button>
    <button <?php if (!$can_edit_media): ?> disabled <?php endif ?> onclick="openFolderDialog()" title="<?= t('create_folder') ?>"><?= $this->include('icons/folder.svg') ?></button>
    <div id="file-form">
        <button onclick="get('#input-file').click()" title="<?= t('upload_file') ?>" <?php if (!$can_edit_media): ?> disabled <?php endif ?>><?= $this->include('icons/upload_file.svg') ?></button>
        <input id="input-file" type="file" class="hidden" name="file" oninput="uploadFile()"/>
    </div>
</div>

<dialog id="edit-dialog">
    <div class="top">
        <div class="title">
            <h2><?= t('rename') ?></h2>
            <span onclick="get('#edit-dialog').close()">
                <?= $this->include('icons/x.svg') ?>
            </span>
        </div>
    </div>
    <div class="content input-group">
        <label for="file-name-input"><?= t('name') ?></label>
        <input id="file-name-input" type="text" name="name"/>
    </div>
    <div class="bottom">
        <button class="light" onclick="get('#edit-dialog').close()"><?= t('cancel') ?></button>
        <button onclick="editFile()"><?= t('save') ?></button>
    </div>
</dialog>
<dialog id="folder-dialog">
    <div class="top">
        <div class="title">
            <h2><?= t('create_folder') ?></h2>
            <span onclick="get('#folder-dialog').close()">
                <?= $this->include('icons/x.svg') ?>
            </span>
        </div>
    </div>
    <div class="content input-group">
        <label for="folder-input"><?= t('name') ?></label>
        <input id="folder-input" type="text" name="name"/>
    </div>
    <div class="bottom">
        <button class="light" onclick="get('#folder-dialog').close()"><?= t('cancel') ?></button>
        <button onclick="createFolder()"><?= t('create') ?></button>
    </div>
</dialog>
<dialog id="move-dialog">
    <div class="top">
        <div class="title">
            <h2><?= t('move') ?></h2>
            <span onclick="get('#move-dialog').close()">
                <?= $this->include('icons/x.svg') ?>
            </span>
        </div>
    </div>
    <div class="content input-group">
        <label for="move-input"><?= t('folder') ?></label>
        <select id="move-input" name="name">
            <?php foreach ($folders as $full_path => $relative_path): ?>
                <option value="<?= e($full_path) ?>"><?= e($relative_path) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="bottom">
        <button class="light" onclick="get('#move-dialog').close()"><?= t('cancel') ?></button>
        <button onclick="moveFile()"><?= t('move') ?></button>
    </div>
</dialog>
<dialog id="duplicate-dialog">
    <div class="top">
        <div class="title">
            <h2><?= t('duplicate') ?></h2>
            <span onclick="get('#duplicate-dialog').close()">
                <?= $this->include('icons/x.svg') ?>
            </span>
        </div>
    </div>
    <div class="content input-group">
        <label for="duplicate-input"><?= t('name') ?></label>
        <input id="duplicate-input" type="text" name="name"/>
    </div>
    <div class="bottom">
        <button class="light" onclick="get('#duplicate-dialog').close()"><?= t('cancel') ?></button>
        <button onclick="duplicateFile()"><?= t('save') ?></button>
    </div>
</dialog>

<script>
    let csrf_token = <?= js($this->csrfToken()) ?>;
    let path = <?= js($_GET['path'] ?? \Aurora\System\Kernel::config('content')) ?>;
    let file_name = null;
    let content = get('.content');

    function uploadFile() {
        Form.send('/admin/media/upload' + window.location.search, 'file-form', get('#file-form button'), {
            csrf: csrf_token,
        }).then(res => {
            Listing.handleResponse(res);
            get('#input-file').value = '';
        });
    }

    function deleteFile(i) {
        file_name = get('#file-name-' + i).innerText;
        if (confirm(<?= js(t('delete_confirm', false)) ?>.sprintf(file_name))) {
            Form.send('/admin/media/remove?path=' + path + '/' + file_name, null, null, {
                csrf: csrf_token,
            }).then(res => Listing.handleResponse(res));
        }
    }

    function openEditFileDialog(i) {
        file_name = get('#file-name-input').value = get('#file-name-' + i).innerText;
        get('#edit-dialog').showModal();
    }

    function editFile() {
        Form.send('/admin/media/save?path=' + path + '/' + file_name, 'edit-dialog', null, {
            csrf: csrf_token,
        }).then(res => Listing.handleResponse(res));
    }

    function openFolderDialog() {
        get('#folder-input').value = '';
        get('#folder-dialog').showModal();
    }

    function createFolder() {
        Form.send('/admin/media/createFolder' + window.location.search, 'folder-dialog', null, {
            csrf: csrf_token,
        }).then(res => Listing.handleResponse(res));
    }

    function openMoveDialog(i) {
        file_name = get('#file-name-' + i).innerText;
        get('#move-dialog').showModal();
    }

    function moveFile() {
        Form.send('/admin/media/move?path=' + path + '/' + file_name, 'move-dialog', null, {
            csrf: csrf_token,
        }).then(res => Listing.handleResponse(res));
    }

    function openDuplicateDialog(i) {
        file_name = get('#duplicate-input').value = get('#file-name-' + i).innerText;
        get('#duplicate-dialog').showModal();
    }

    function duplicateFile() {
        Form.send('/admin/media/duplicate?path=' + path + '/' + file_name, 'duplicate-dialog', null, {
            csrf: csrf_token,
        }).then(res => Listing.handleResponse(res));
    }

    function copyPath(path) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(path).then(() => Snackbar.show(LANG.done));
            return;
        }

        let input = document.createElement('input');
        input.setAttribute('value', path);
        document.body.appendChild(input);
        input.select();
        let result = document.execCommand('copy');
        document.body.removeChild(input);
        if (result) {
            Snackbar.show(LANG.path_copied);
        }
    }

    function downloadAll() {
        if (confirm(<?= js(t('download_media_description', false)) ?>)) {
            location = '/admin/settings/media_download?path=' + path;
        }
    }

    content.addEventListener('dragover', event => event.preventDefault());

    content.addEventListener('drop', function(event) {
        event.preventDefault();

        document.body.style.cursor = 'wait';
        let data = new FormData();
        data.append('csrf', <?= js($this->csrfToken()) ?>);
        Array.from(event.dataTransfer.files).map(file => data.append('file[]', file));

        fetch('/admin/media/upload' + window.location.search, {
            method: 'POST',
            body: data,
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                Snackbar.show(LANG.done);
            } else if (res.errors && res.errors.hasOwnProperty(0)) {
                Snackbar.show(res.errors[0], false);
            }

            Listing.handleResponse(res);
        })
        .catch(() => Snackbar.show(LANG.unexpected_error, false))
        .finally(() => document.body.style.cursor = 'default');
    });
</script>
