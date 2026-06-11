<?php
// views/movie/index.php
?>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 fw-bold text-light mb-3">Danh Sách Phim</h1>
        
        <!-- Filters panel -->
        <div class="card bg-black border border-secondary p-3 rounded shadow-sm">
            <div class="row align-items-center g-3">
                <!-- Status Tab buttons -->
                <div class="col-md-6">
                    <div class="btn-group shadow-sm" role="group">
                        <a href="/movies?status=now_showing<?= $genre ? '&genre='.urlencode($genre) : '' ?>" 
                           class="btn btn-sm <?= $status === 'now_showing' ? 'btn-warning fw-bold' : 'btn-outline-secondary' ?>">
                            <i class="bi bi-play-fill"></i> Đang chiếu
                        </a>
                        <a href="/movies?status=coming_soon<?= $genre ? '&genre='.urlencode($genre) : '' ?>" 
                           class="btn btn-sm <?= $status === 'coming_soon' ? 'btn-warning fw-bold' : 'btn-outline-secondary' ?>">
                            <i class="bi bi-calendar-event"></i> Sắp chiếu
                        </a>
                        <a href="/movies?status=ended<?= $genre ? '&genre='.urlencode($genre) : '' ?>" 
                           class="btn btn-sm <?= $status === 'ended' ? 'btn-warning fw-bold' : 'btn-outline-secondary' ?>">
                            <i class="bi bi-stop-fill"></i> Đã dừng chiếu
                        </a>
                    </div>
                </div>
                
                <!-- Genres dropdown filter -->
                <div class="col-md-6 text-md-end">
                    <label class="text-secondary small me-2">Thể loại:</label>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-outline-warning dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown">
                            <?= $genre ?: 'Tất cả' ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end border-secondary bg-black shadow">
                            <li><a class="dropdown-item" href="/movies?status=<?= $status ?>">Tất cả</a></li>
                            <li><a class="dropdown-item" href="/movies?status=<?= $status ?>&genre=<?= urlencode('Hành động') ?>">Hành động</a></li>
                            <li><a class="dropdown-item" href="/movies?status=<?= $status ?>&genre=<?= urlencode('Hài') ?>">Hài</a></li>
                            <li><a class="dropdown-item" href="/movies?status=<?= $status ?>&genre=<?= urlencode('Kinh dị') ?>">Kinh dị</a></li>
                            <li><a class="dropdown-item" href="/movies?status=<?= $status ?>&genre=<?= urlencode('Tình cảm') ?>">Tình cảm</a></li>
                            <li><a class="dropdown-item" href="/movies?status=<?= $status ?>&genre=<?= urlencode('Viễn tưởng') ?>">Viễn tưởng</a></li>
                            <li><a class="dropdown-item" href="/movies?status=<?= $status ?>&genre=<?= urlencode('Hoạt hình') ?>">Hoạt hình</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (empty($movies)): ?>
    <div class="text-center py-5">
        <i class="bi bi-camera-reels text-secondary display-1 mb-3"></i>
        <p class="text-secondary fs-5 fst-italic">Không tìm thấy phim phù hợp với bộ lọc hiện tại.</p>
    </div>
<?php else: ?>
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4">
        <?php foreach ($movies as $movie): ?>
            <div class="col">
                <div class="card bg-black border border-secondary h-100 shadow movie-card"
                     onclick="location.href='/movies/<?= $movie->id ?>'">
                    <div class="position-relative">
                        <img src="<?= htmlspecialchars($movie->posterUrl ?: 'https://placehold.co/400x600/111/fff?text=No+Poster') ?>"
                             class="card-img-top img-fluid rounded-top"
                             alt="<?= htmlspecialchars($movie->title) ?>"
                             style="height: 300px; object-fit: cover;">
                        
                        <?php if ($movie->ageRating): ?>
                            <span class="badge position-absolute top-0 end-0 m-2 badge-<?= strtolower($movie->ageRating) ?>">
                                <?= htmlspecialchars($movie->ageRating) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-3 bg-black">
                        <h6 class="card-title text-light mb-2 text-truncate fw-bold">
                            <?= htmlspecialchars($movie->title) ?>
                        </h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-secondary">
                                <i class="bi bi-clock me-1"></i>
                                <?= htmlspecialchars($movie->getFormattedDuration()) ?>
                            </small>
                            <span class="badge bg-warning text-dark font-monospace" style="font-size: 0.75rem;">XEM CHI TIẾT</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
