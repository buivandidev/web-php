<?php
// views/auth/register.php
?>
<div class="row justify-content-center py-5">
    <div class="col-md-6 col-lg-5 col-xl-4">
        <div class="card bg-black border border-secondary p-4 rounded shadow-lg">
            <h2 class="text-center text-warning fw-bold mb-4">ĐĂNG KÝ</h2>

            <?php if (isset($vm->errors['general'])): ?>
                <div class="alert alert-danger border-0 p-2 text-center mb-3" style="background-color: #3b171c; color: #f8d7da;">
                    <small><?= htmlspecialchars($vm->errors['general']) ?></small>
                </div>
            <?php endif; ?>

            <form method="POST" action="/register">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label text-light">Tên người dùng</label>
                    <input type="text" name="username" 
                           class="form-control bg-dark text-light border-secondary <?= isset($vm->errors['username']) ? 'is-invalid' : '' ?>" 
                           placeholder="Nhập tên người dùng..." 
                           value="<?= htmlspecialchars($vm->username ?? '') ?>" required>
                    <?php if (isset($vm->errors['username'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($vm->errors['username']) ?></div>
                    <?php endif; ?>
                </div>

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

                <div class="mb-3">
                    <label class="form-label text-light">Mật khẩu</label>
                    <input type="password" name="password" 
                           class="form-control bg-dark text-light border-secondary <?= isset($vm->errors['password']) ? 'is-invalid' : '' ?>" 
                           placeholder="Tối thiểu 8 ký tự" required>
                    <?php if (isset($vm->errors['password'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($vm->errors['password']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label class="form-label text-light">Xác nhận mật khẩu</label>
                    <input type="password" name="confirm_password" 
                           class="form-control bg-dark text-light border-secondary <?= isset($vm->errors['confirmPassword']) ? 'is-invalid' : '' ?>" 
                           placeholder="Xác nhận mật khẩu" required>
                    <?php if (isset($vm->errors['confirmPassword'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($vm->errors['confirmPassword']) ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-warning w-100 fw-bold py-2 shadow mb-3">
                    ĐĂNG KÝ
                </button>

                <p class="text-center text-secondary small mb-0">
                    Đã có tài khoản? <a href="/login" class="text-warning text-decoration-none fw-semibold">Đăng nhập ngay</a>
                </p>
            </form>
        </div>
    </div>
</div>
