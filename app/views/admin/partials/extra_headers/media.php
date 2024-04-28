<div class="batch-options-container">
    <div id="batch-options">
        <?php if (\Aurora\App\Permission::can('edit_media')): ?>
            <button
                onclick="
                        var files = Listing.getSelectedRows().map(el => el.dataset.id);
                        return files.length == 0 ? false : openMoveDialog(files);
                    "
            >Move</button>
            <button
                class="danger"
                onclick="
                        var files = Listing.getSelectedRows().map(el => el.dataset.id);
                        return files.length == 0 ? false : deleteFiles(files);
                    "
            >Delete</button>
        <?php endif ?>
    </div>
    <button onclick="Listing.toggleSelectMode(this)">Select</button>
</div>
