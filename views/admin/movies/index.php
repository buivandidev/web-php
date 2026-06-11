<?php
// views/admin/movies/index.php
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom border-secondary">
    <h1 class="h2 text-warning fw-bold">Quản Lý Phim</h1>
    <button type="button" class="btn btn-warning fw-bold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#addMovieModal">
        <i class="bi bi-plus-lg"></i> Thêm phim mới
    </button>
</div>

<?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger border-0 mb-3" style="background-color: #3b171c; color: #f8d7da;">
        <?= htmlspecialchars($errors['general']) ?>
    </div>
<?php endif; ?>

<!-- Movies list table -->
<div class="card bg-black border border-secondary rounded shadow-sm">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 align-middle">
            <thead class="table-light text-uppercase" style="--bs-table-bg: #111;">
                <tr>
                    <th scope="col" style="width: 80px;">Poster</th>
                    <th scope="col">Tiêu đề</th>
                    <th scope="col">Thể loại</th>
                    <th scope="col">Trạng thái</th>
                    <th scope="col">Thời lượng</th>
                    <th scope="col">Độ tuổi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movies)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary fst-italic">
                            Chưa có bộ phim nào trong danh sách.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movies as $movie): ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($movie->posterUrl ?: 'https://placehold.co/100x150/111/fff?text=No+Poster') ?>" 
                                     alt="Poster" class="img-thumbnail bg-dark border-secondary rounded shadow-sm" style="width: 50px; height: 75px; object-fit: cover;">
                            </td>
                            <td>
                                <strong class="text-light d-block"><?= htmlspecialchars($movie->title) ?></strong>
                                <small class="text-secondary" style="font-size: 0.75rem;">ID: <?= $movie->id ?></small>
                            </td>
                            <td><?= htmlspecialchars($movie->genre ?: '-') ?></td>
                            <td>
                                <?php if ($movie->status === 'now_showing'): ?>
                                    <span class="badge bg-success">Đang chiếu</span>
                                <?php elseif ($movie->status === 'coming_soon'): ?>
                                    <span class="badge bg-info text-dark">Sắp chiếu</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Dừng chiếu</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($movie->getFormattedDuration()) ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower($movie->ageRating ?? 'P') ?>">
                                    <?= htmlspecialchars($movie->ageRating ?: 'P') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Thêm phim mới -->
<div class="modal fade" id="addMovieModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-light border border-secondary shadow-lg">
            <div class="modal-header bg-black border-bottom border-secondary">
                <h5 class="modal-title text-warning fw-bold"><i class="bi bi-film me-2"></i>Thêm Phim Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form method="POST" action="/admin/movies" enctype="multipart/form-data">
                <?= csrf_field() ?>
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Tiêu đề -->
                        <div class="col-md-6">
                            <label class="form-label text-light fw-bold">Tiêu đề phim</label>
                            <input type="text" name="title" class="form-control bg-black text-light border-secondary" required placeholder="Nhập tiêu đề...">
                        </div>
                        
                        <!-- Thể loại -->
                        <div class="col-md-6">
                            <label class="form-label text-light fw-bold">Thể loại</label>
                            <select name="genre" class="form-select bg-black text-light border-secondary">
                                <option value="Hành động">Hành động</option>
                                <option value="Hài">Hài</option>
                                <option value="Kinh dị">Kinh dị</option>
                                <option value="Tình cảm">Tình cảm</option>
                                <option value="Viễn tưởng">Viễn tưởng</option>
                                <option value="Hoạt hình">Hoạt hình</option>
                            </select>
                        </div>

                        <!-- Trạng thái -->
                        <div class="col-md-4">
                            <label class="form-label text-light fw-bold">Trạng thái</label>
                            <select name="status" class="form-select bg-black text-light border-secondary">
                                <option value="coming_soon">Sắp chiếu</option>
                                <option value="now_showing">Đang chiếu</option>
                                <option value="ended">Dừng chiếu</option>
                            </select>
                        </div>

                        <!-- Thời lượng -->
                        <div class="col-md-4">
                            <label class="form-label text-light fw-bold">Thời lượng (phút)</label>
                            <input type="number" name="duration_minutes" class="form-control bg-black text-light border-secondary" required min="1" placeholder="Ví dụ: 120">
                        </div>

                        <!-- Độ tuổi -->
                        <div class="col-md-4">
                            <label class="form-label text-light fw-bold">Giới hạn độ tuổi</label>
                            <select name="age_rating" class="form-select bg-black text-light border-secondary">
                                <option value="P">P - Mọi lứa tuổi</option>
                                <option value="C13">C13 - Trên 13 tuổi</option>
                                <option value="C16">C16 - Trên 16 tuổi</option>
                                <option value="C18">C18 - Trên 18 tuổi</option>
                            </select>
                        </div>

                        <!-- Ảnh Poster -->
                        <div class="col-12">
                            <label class="form-label text-light fw-bold">Ảnh Poster phim</label>
                            <input type="file" name="poster" class="form-control bg-black text-light border-secondary" accept="image/*">
                            <small class="text-secondary">Hỗ trợ các định dạng ảnh JPEG, PNG, WEBP.</small>
                        </div>

                        <!-- Mô tả -->
                        <div class="col-12">
                            <label class="form-label text-light fw-bold">Mô tả chi tiết</label>
                            <textarea name="description" rows="4" class="form-control bg-black text-light border-secondary" placeholder="Tóm tắt nội dung phim..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-black border-top border-secondary">
                    <button type="button" class="btn btn-outline-secondary text-light fw-semibold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($errors) && !empty($errors)): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Show modal if there are errors (e.g. title is blank)
    const modal = new bootstrap.Modal(document.getElementById('addMovieModal'));
    modal.show();
});
</script>
<?php endif; ?>
