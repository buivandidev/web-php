<?php
// views/partials/seat_map.php
/**
 * Nhận vào $seatMap (SeatMapViewModel)
 * Render sơ đồ ghế bằng Bootstrap Grid + vòng lặp
 */
?>

<!-- #9 Chú thích màu sắc ghế — Phân biệt theo loại -->
<div class="seat-legend-bar d-flex gap-4 justify-content-center mb-4 flex-wrap">
    <div class="d-flex align-items-center gap-2">
        <div style="width:22px; height:22px; border:2px solid #198754; border-radius:4px;"></div>
        <small class="text-light">Trống</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div style="width:22px; height:22px; background:#198754; border-radius:4px; box-shadow: 0 0 8px rgba(25,135,84,0.6);"></div>
        <small class="text-light">Đang chọn</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div style="width:22px; height:22px; background:#ffc107; border-radius:4px; opacity:0.6;"></div>
        <small class="text-light">Đang giữ</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div style="width:22px; height:22px; background:#dc3545; border-radius:4px; opacity:0.5;"></div>
        <small class="text-light">Đã bán</small>
    </div>
</div>

<!-- #1 Màn chiếu phát sáng — Glow effect -->
<div class="text-center mb-5">
    <div class="cinema-screen"></div>
    <small class="cinema-screen-label">MÀN CHIẾU</small>
</div>

<!-- Sơ đồ ghế -->
<div class="seat-map-container overflow-auto p-2 bg-black rounded shadow-lg border border-secondary text-center">
    <?php for ($row = 0; $row < $seatMap->totalRows; $row++): ?>
        <?php $rowLabel = chr(ord('A') + $row); ?>

        <div class="d-flex justify-content-center align-items-center gap-1 mb-2">
            <!-- Nhãn hàng bên trái -->
            <span class="text-secondary me-3 fw-bold" style="width: 24px; text-align: right; font-size: 0.85rem;">
                <?= $rowLabel ?>
            </span>

            <?php for ($col = 1; $col <= $seatMap->seatsPerRow; $col++): ?>
                <?php
                    $seatCode = $rowLabel . $col;
                    $status   = $seatMap->getSeatStatus($seatCode);

                    // Map status → CSS class
                    // Xử lý loại ghế theo hàng:
                    // - Hàng cuối cùng: Sweetbox
                    // - Hàng A->D (0->3): Standard
                    // - Còn lại: VIP
                    $baseClass = match(true) {
                        $row === $seatMap->totalRows - 1 => 'seat-sweetbox',
                        $row <= 3 => 'btn-outline-success', // Standard
                        default => 'seat-vip',
                    };

                    [$btnClass, $disabled, $dataAttr] = match($status) {
                        'paid'    => ['seat-sold',           'disabled', ''],
                        'holding' => ['seat-holding',        'disabled', ''],
                        default   => [$baseClass,            '',         "data-seat=\"$seatCode\""],
                    };
                ?>
                <button
                    type="button"
                    class="btn btn-sm seat-btn <?= $btnClass ?>"
                    <?= $disabled ?>
                    <?= $dataAttr ?>
                    title="Ghế <?= $seatCode ?>"
                    style="width: 38px; height: 38px; padding: 0; font-size: 0.75rem;">
                    <?= $seatCode ?>
                </button>
            <?php endfor; ?>

            <!-- Nhãn hàng bên phải -->
            <span class="text-secondary ms-3 fw-bold" style="width: 24px; text-align: left; font-size: 0.85rem;">
                <?= $rowLabel ?>
            </span>
        </div>

    <?php endfor; ?>
</div>
