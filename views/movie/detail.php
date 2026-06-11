<?php
// views/movie/detail.php
?>
<div class="row g-4 mb-5">
    <!-- Cột trái: Poster với 3D tilt -->
    <div class="col-md-4 col-lg-3 text-center text-md-start">
        <div class="movie-card-3d">
            <img src="<?= htmlspecialchars($movie->posterUrl ?: 'https://placehold.co/400x600/111/fff?text=No+Poster') ?>"
                 class="img-fluid rounded shadow-lg border border-secondary movie-card"
                 alt="<?= htmlspecialchars($movie->title) ?>"
                 data-tilt
                 style="max-height: 420px; object-fit: cover; width: 100%;">
        </div>
    </div>

    <!-- Cột phải: Thông tin chi tiết -->
    <div class="col-md-8 col-lg-9">
        <h1 class="display-5 fw-bold text-light mb-3"><?= htmlspecialchars($movie->title) ?></h1>
        
        <div class="d-flex flex-wrap gap-2 mb-4">
            <?php if ($movie->ageRating): ?>
                <span class="badge badge-<?= strtolower($movie->ageRating) ?> fs-6 p-2 rounded">
                    <?= htmlspecialchars($movie->ageRating) ?>
                </span>
            <?php endif; ?>
            <span class="badge bg-secondary fs-6 p-2 rounded">
                <i class="bi bi-clock me-1"></i> <?= htmlspecialchars($movie->formattedDuration) ?>
            </span>
            <?php if ($movie->genre): ?>
                <span class="badge bg-secondary fs-6 p-2 rounded">
                    <i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($movie->genre) ?>
                </span>
            <?php endif; ?>
            <span class="badge bg-dark border border-secondary text-warning fs-6 p-2 rounded">
                <?= $movie->status === 'now_showing' ? 'Đang chiếu' : ($movie->status === 'coming_soon' ? 'Sắp chiếu' : 'Đã kết thúc') ?>
            </span>
        </div>

        <h5 class="text-warning border-start border-warning border-3 ps-2 mb-2 fw-bold">Mô tả phim</h5>
        <p class="text-light-50 lh-lg mb-4" style="text-align: justify; color: #ced4da;">
            <?= nl2br(htmlspecialchars($movie->description ?: 'Chưa có thông tin mô tả chi tiết cho bộ phim này.')) ?>
        </p>
    </div>
</div>

<!-- #7 Lịch chiếu trượt ngang + #8 Thẻ suất chiếu đẹp -->
<div class="card bg-black border border-secondary rounded p-4 shadow-lg">
    <h3 class="h4 text-warning border-start border-warning border-4 ps-3 fw-bold mb-4">
        <i class="bi bi-calendar3 me-2"></i>LỊCH CHIẾU & SUẤT CHIẾU
    </h3>

    <!-- #7 Date slider — Trượt ngang, scrollbar ẩn -->
    <div class="date-slider mb-4 pb-3 border-bottom border-secondary">
        <?php
        $daysOfWeek = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
        for ($i = 0; $i < 7; $i++):
            $timestamp = strtotime("+$i days");
            $dateVal = date('Y-m-d', $timestamp);
            $dayLabel = ($i === 0) ? 'Hôm nay' : $daysOfWeek[date('w', $timestamp)];
            $dayNumber = date('d', $timestamp);
            $monthLabel = 'Th' . date('n', $timestamp);
            $activeClass = ($dateVal === $selectedDate) ? 'active' : '';
        ?>
            <a href="/movies/<?= $movie->id ?>?date=<?= $dateVal ?>"
               class="date-item <?= $activeClass ?>">
                <span class="day-name"><?= $dayLabel ?></span>
                <span class="day-number"><?= $dayNumber ?></span>
                <span class="day-name"><?= $monthLabel ?></span>
            </a>
        <?php endfor; ?>
    </div>

    <!-- #8 Showtime cards -->
    <div class="showtimes-container">
        <?php if ($movie->status !== 'now_showing'): ?>
            <div class="text-center py-4 text-secondary">
                <i class="bi bi-calendar-x display-6 mb-2 d-block"></i>
                Phim hiện không ở trạng thái "Đang chiếu" nên không có suất chiếu.
            </div>
        <?php elseif (empty($movie->showtimes)): ?>
            <div class="text-center py-4 text-secondary">
                <i class="bi bi-clock-history display-6 mb-2 d-block"></i>
                Không có suất chiếu nào vào ngày <?= date('d/m/Y', strtotime($selectedDate)) ?>. Vui lòng chọn ngày khác.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                <?php foreach ($movie->showtimes as $showtime): ?>
                    <div class="col">
                        <div class="showtime-card">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="room-badge">
                                        <?= htmlspecialchars($showtime->roomName) ?>
                                    </span>
                                    <span class="seats-left <?= $showtime->availableSeats <= 20 ? 'low' : '' ?>">
                                        <i class="bi bi-people-fill me-1"></i><?= $showtime->availableSeats ?> ghế trống
                                    </span>
                                </div>
                                <div class="time"><?= date('H:i', strtotime($showtime->startTime)) ?></div>
                                <div class="price"><?= htmlspecialchars($showtime->formattedPrice) ?></div>
                            </div>
                            <a href="/booking/<?= $showtime->id ?>"
                               class="btn btn-warning w-100 fw-bold btn-select-showtime <?= $showtime->availableSeats === 0 ? 'disabled btn-secondary text-muted' : '' ?>">
                                <?= $showtime->availableSeats === 0 ? 'Hết vé' : 'Chọn ghế' ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
