<?php
// views/home/index.php
?>
<!-- #10 Hero Banner — Hiệu ứng gradient + bokeh glow -->
<div class="hero-banner mb-5 shadow-lg">
    <h1>🎬 CinemaX</h1>
    <p>Trải nghiệm đặt vé xem phim trực tuyến hiện đại & nhanh chóng nhất</p>
    <a href="/movies" class="btn btn-warning btn-lg fw-bold px-5 shadow animate-pulse-glow">
        <i class="bi bi-ticket-perforated me-2"></i>Đặt Vé Ngay
    </a>
</div>

<!-- Phim đang chiếu -->
<div class="mb-5">
    <h2 class="h4 mb-4 text-warning border-start border-warning border-4 ps-3 fw-bold">
        <i class="bi bi-play-circle-fill me-2"></i>PHIM ĐANG CHIẾU
    </h2>

    <?php if (empty($nowShowing)): ?>
        <p class="text-secondary fst-italic">Hiện tại không có phim nào đang chiếu.</p>
    <?php else: ?>
        <!-- #3 Poster 3D — movie-card-3d wrapper -->
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4">
            <?php foreach ($nowShowing as $movie): ?>
                <div class="col">
                    <div class="movie-card-3d">
                        <div class="card bg-black border border-secondary h-100 shadow movie-card"
                             onclick="location.href='/movies/<?= $movie->id ?>'"
                             data-tilt>
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($movie->posterUrl ?: 'https://placehold.co/400x600/111/fff?text=No+Poster') ?>"
                                     class="card-img-top img-fluid rounded-top"
                                     alt="<?= htmlspecialchars($movie->title) ?>"
                                     style="height: 300px; object-fit: cover;">
                                
                                <!-- Age rating badge -->
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
                                    <span class="badge bg-warning text-dark font-monospace" style="font-size: 0.75rem;">ĐẶT VÉ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Phim sắp chiếu -->
<div>
    <h2 class="h4 mb-4 text-info border-start border-info border-4 ps-3 fw-bold">
        <i class="bi bi-calendar-event-fill me-2"></i>PHIM SẮP CHIẾU
    </h2>

    <?php if (empty($comingSoon)): ?>
        <p class="text-secondary fst-italic">Không có phim sắp chiếu trong danh sách.</p>
    <?php else: ?>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4">
            <?php foreach ($comingSoon as $movie): ?>
                <div class="col">
                    <div class="movie-card-3d">
                        <div class="card bg-black border border-secondary h-100 shadow movie-card"
                             onclick="location.href='/movies/<?= $movie->id ?>'"
                             data-tilt>
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($movie->posterUrl ?: 'https://placehold.co/400x600/111/fff?text=No+Poster') ?>"
                                     class="card-img-top img-fluid rounded-top"
                                     alt="<?= htmlspecialchars($movie->title) ?>"
                                     style="height: 300px; object-fit: cover;">
                                
                                <!-- Age rating badge -->
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
                                    <span class="badge bg-info text-dark font-monospace" style="font-size: 0.75rem;">SẮP CHIẾU</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
