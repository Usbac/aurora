<div class="batch-options-container">
    <div id="batch-options">
        <?php if (\Aurora\App\Permission::can('edit_users')): ?>
            <button
                class="danger"
                onclick="confirm(LANG.delete_confirm_selected) && Form.send('/admin/users/remove', null, null, {
                        csrf: csrf_token,
                        id: Listing.getSelectedRows().map(el => el.dataset.id),
                    }).then(res => Listing.handleResponse(res));"
            >Delete</button>
        <?php endif ?>
    </div>
    <button onclick="Listing.toggleSelectMode(this)">Select</button>
</div>
