<?php
// views/auth/login.php
?>
<div class="row justify-content-center py-5">
    <div class="col-md-6 col-lg-5 col-xl-4">
        <div class="card bg-black border border-secondary p-4 rounded shadow-lg">
            <h2 class="text-center text-warning fw-bold mb-4">ĐĂNG NHẬP</h2>

            <?php if (isset($vm->errors['general'])): ?>
                <div class="alert alert-danger border-0 p-2 text-center mb-3" style="background-color: #3b171c; color: #f8d7da;">
                    <small><?= htmlspecialchars($vm->errors['general']) ?></small>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login<?= isset($_GET['redirect']) ? '?redirect='.urlencode($_GET['redirect']) : '' ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label text-light">Email</label>
                    <input type="email" name="email" 
                           class="form-control bg-dark text-light border-secondary <?= isset($vm->errors['email']) ? 'is-invalid' : '' ?>" 
                           placeholder="name@example.com" 
                           value="<?= htmlspecialchars($vm->email ?? '') ?>" required>
                    <?php if (isset($vm->errors['email'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($vm->errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label class="form-label text-light">Mật khẩu</label>
                    <input type="password" name="password" 
                           class="form-control bg-dark text-light border-secondary <?= isset($vm->errors['password']) ? 'is-invalid' : '' ?>" 
                           placeholder="Mật khẩu" required>
                    <?php if (isset($vm->errors['password'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($vm->errors['password']) ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-warning w-100 fw-bold py-2 shadow mb-3">
                    ĐĂNG NHẬP
                </button>

                <p class="text-center text-secondary small mb-0">
                    Chưa có tài khoản? <a href="/register" class="text-warning text-decoration-none fw-semibold">Đăng ký ngay</a>
                </p>
            </form>
        </div>
    </div>
</div>
