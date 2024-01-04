<div id="snackbar">
    <div class="error">
        <?= $this->include('icons/x-circle.svg') ?>
    </div>
    <div class="success">
        <?= $this->include('icons/check-circle.svg') ?>
    </div>
    <span></span>
    <div class="close" onclick="Snackbar.hide()">
        <?= $this->include('icons/x.svg') ?>
    </div>
</div>
