<div class="top">
    <div class="title">
        <h2><?= t('image_picker') ?></h2>
        <span onclick="ImageDialog.close()">
            <?= $this->include('icons/x.svg') ?>
        </span>
    </div>
    <div class="header">
        <div class="media-paths">
            <?php $folders = explode('/', trim($path, '/')) ?>
            <?php foreach ($folders as $i => $folder): ?>
                <?php $folder_path = implode('/', array_slice($folders, 0, $i + 1)) ?>
                <?php if (\Aurora\App\Media::isValidPath(\Aurora\System\Helper::getPath($folder_path))): ?>
                    <a href="#" class="pointer" onclick="ImageDialog.setImagePage(<?= e(js($folder_path)) ?>)"><?= e($folder) ?></a>
                    <span>/</span>
                <?php endif ?>
            <?php endforeach ?>
        </div>
        <div id="image-dialog-file-form">
            <button class="light" onclick="ImageDialog.close(); ImageDialog.clearImage();"><?= t('remove_image') ?></button>
            <button id="image-dialog-file-button" onclick="get('#image-input-file').click()" <?php if (!\Aurora\App\Permission::can('edit_media')): ?> disabled <?php endif ?>><?= $this->include('icons/upload_file.svg') ?></button>
            <input
                id="image-input-file"
                type="file"
                class="hidden"
                name="file"
                oninput="let path = <?= e(js($path)) ?>; Form.send(`/admin/media/upload?path=${path}`, 'image-dialog-file-form', get('#image-dialog-file-button')).then(() => ImageDialog.setImagePage(path));"
            />
        </div>
    </div>
</div>
<div id="image-dialog-listing" class="listing">
    <div class="listing-row header">
        <div class="w60"></div>
        <div class="w20" title="<?= t('information') ?>"><?= t('information') ?></div>
        <div class="w20" title="<?= t('last_modification') ?>"><?= t('last_modification') ?></div>
    </div>
    <?php foreach ($files as $i => $file): ?>
        <?php $file_path = '/' . \Aurora\System\Kernel::config('content') . '/' . trim($file['path'], '/') ?>
        <div
            class="listing-row file"
            <?php if ($file['is_file']): ?>
                onclick="ImageDialog.close(); ImageDialog.setImage(<?= e(js($file['path'])) ?>);"
            <?php else: ?>
                onclick="ImageDialog.setImagePage(<?= e(js($file_path)) ?>)"
            <?php endif ?>
        >
            <div class="w60 align-center">
                <?php if ($file['is_file']): ?>
                    <a href="<?= e($file_path) ?>" target="_blank" class="pointer" onclick="event.stopPropagation()">
                        <img src="<?= e($file_path) ?>"/>
                    </a>
                <?php else: ?>
                    <a href="#" class="pointer custom-media folder">
                        <?= $this->include('icons/folder-fill.svg') ?>
                    </a>
                <?php endif ?>
                <span class="file-name"><?= e($file['name']) ?></span>
            </div>
            <div class="w20 file-info">
                <?php if ($file['is_file']): ?>
                    <p><?= e(\Aurora\System\Helper::getByteSize($file['size'])) ?></p>
                <?php endif ?>
                <p><?= e($file['mime']) ?></p>
            </div>
            <div class="w20">
                <?= e($this->dateFormat($file['time'])) ?>
            </div>
        </div>
    <?php endforeach ?>
</div>
