<?php if (session()->has('success')) : ?>
  <div class="alert alert-success alert-dismissible fade show border-0 mb-4" role="alert">
    <div class="d-flex align-items-center">
      <div class="flex-grow-1">
        <?= (string) esc(session()->get('success')) ?>
      </div>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
<?php endif; ?>

<?php if (session()->has('error')) : ?>
  <div class="alert alert-danger alert-dismissible fade show border-0 mb-4" role="alert">
    <div class="d-flex align-items-center">
      <div class="flex-grow-1">
        <?= (string) esc(session()->get('error')) ?>
      </div>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
<?php endif; ?>