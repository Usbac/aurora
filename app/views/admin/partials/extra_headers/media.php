<div class="batch-options-container">
    <div id="batch-options" data-disabled="true">
        <?php if (\Aurora\App\Permission::can('edit_media')): ?>
            <button
                class="danger"
                onclick="
                        let ids = Listing.getSelectedRows().map(el => path + '/' + el.dataset.id);

                        if (ids.length == 0) {
                            return false;
                        }

                        if (confirm(LANG.delete_confirm_selected)) {
                            Form.send('/admin/media/remove', null, null, {
                                csrf: <?= e(js($this->csrfToken())) ?>,
                                paths: JSON.stringify(ids),
                            }).then(res => Listing.handleResponse(res));
                        }
                    "
            >Delete</button>
        <?php endif ?>
    </div>
    <button onclick="Listing.toggleSelectMode(this)">Select</button>
</div>
