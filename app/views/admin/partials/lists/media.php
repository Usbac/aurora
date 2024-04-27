<?php foreach ($files as $i => $file): ?>
    <?php $file_path = '/' . \Aurora\System\Kernel::config('content') . '/' . trim($file['path'], '/') ?>
    <div data-id="<?= e($file['name']) ?>" class="listing-row file" onclick="Listing.toggleRow(this, event)">
        <div class="w100 align-center">
            <?php if ($file['is_image']): ?>
                <a href="<?= e($file_path) ?>" target="_blank" class="pointer">
                    <img src="<?= e($file_path) ?>"/>
                </a>
            <?php elseif ($file['is_file']): ?>
                <a href="<?= e($file_path) ?>" target="_blank" class="pointer custom-media file">
                    <?= $this->include('icons/file.svg') ?>
                </a>
            <?php else: ?>
                <a href="/admin/media?path=<?= e($file_path) ?>" class="pointer custom-media folder">
                    <?= $this->include('icons/folder-fill.svg') ?>
                </a>
            <?php endif ?>
            <span id="file-name-<?= e($i) ?>"><?= e($file['name']) ?></span>
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
        <div class="w10 row-actions">
            <div class="three-dots" onclick="return false" dropdown>
                <?= $this->include('icons/dots.svg') ?>
                <div class="dropdown-menu">
                    <div onclick="copyPath(<?= e(js($this->url($file_path))) ?>)"><?= $this->include('icons/clipboard.svg') ?> <?= t('copy_path') ?></div>
                    <?php if (\Aurora\App\Permission::can('edit_media')): ?>
                        <div onclick="openDuplicateDialog(<?= e(js($i)) ?>)"><?= $this->include('icons/duplicate.svg') ?> <?= t('duplicate') ?>…</div>
                        <div onclick="openMoveDialog([ <?= e(js($file['name'])) ?> ])"><?= $this->include('icons/move_file.svg') ?> <?= t('move') ?>…</div>
                        <div onclick="openEditFileDialog(<?= e(js($i)) ?>)"><?= $this->include('icons/pencil.svg') ?> <?= t('rename') ?>…</div>
                        <div onclick="deleteFiles([ <?= e(js($file['name'])) ?> ])" class="danger"><?= $this->include('icons/trash.svg') ?> <?= t('delete') ?></div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach ?>
