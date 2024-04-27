<div class="batch-options-container">
    <div id="batch-options" data-disabled="true">
        <?php if (\Aurora\App\Permission::can('edit_pages')): ?>
            <button
                class="danger"
                onclick="
                        let ids = Listing.getSelectedRows().map(el => el.dataset.id);

                        if (ids.length == 0) {
                            return false;
                        }

                        if (confirm(LANG.delete_confirm_selected)) {
                            Form.send('/admin/pages/remove', null, null, {
                                csrf: csrf_token,
                                id: ids,
                            }).then(res => Listing.handleResponse(res));
                        }
                    "
            >Delete</button>
        <?php endif ?>
    </div>
    <button onclick="Listing.toggleSelectMode(this)">Select</button>
</div>