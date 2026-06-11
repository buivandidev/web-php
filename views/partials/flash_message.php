<?php
// views/partials/flash_message.php
use App\Core\Session;

$error   = Session::getFlash('error');
$success = Session::getFlash('success');
?>
<div class="container mt-3">
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow" role="alert" style="background-color: #3b171c; color: #f8d7da;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow" role="alert" style="background-color: #173b22; color: #d1e7dd;">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
</div>
