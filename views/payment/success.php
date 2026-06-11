<?php
// views/payment/success.php
?>
<div class="row justify-content-center py-5">
    <div class="col-md-10 text-center">
        
        <div class="mb-4">
            <i class="bi bi-check-circle-fill text-success display-1 shadow-sm animate-bounce-in"></i>
        </div>
        
        <h1 class="text-success fw-bold mb-5 animate-pulse-glow d-inline-block px-4 py-2 rounded">
            ĐẶT VÉ THÀNH CÔNG!
        </h1>
        
        <p class="lead text-light mb-5">
            Cảm ơn bạn đã lựa chọn CinemaX. Mã vé của bạn đã được ghi nhận trong hệ thống.
        </p>

        <!-- #4 Vé xem phim lật 3D -->
        <div class="ticket-flip mb-5" onclick="this.classList.toggle('flipped')">
            <div class="ticket-inner shadow-lg">

                <!-- Mặt trước -->
                <div class="ticket-front">
                    <h5 class="text-warning fw-bold mb-3" style="letter-spacing: 2px;">
                        <i class="bi bi-film me-2"></i>CINEMAX TICKET
                    </h5>
                    <div class="text-start w-100">
                        <p class="mb-1"><span class="text-secondary small">Mã Giao Dịch:</span> <strong class="text-info"><?= htmlspecialchars($transactionId) ?></strong></p>
                        <p class="mb-1"><span class="text-secondary small">Ghế:</span> <strong>Xác nhận qua email/SMS</strong></p>
                        <p class="mb-0 mt-3 text-center text-secondary small">
                            <i class="bi bi-hand-index-thumb"></i> Chạm để xem mặt sau
                        </p>
                    </div>
                </div>

                <!-- Mặt sau -->
                <div class="ticket-back d-flex flex-column justify-content-center align-items-center">
                    <p class="fw-bold text-warning mb-2" style="letter-spacing: 1px;">MÃ QR KIỂM VÉ</p>
                    <div class="bg-white p-2 rounded d-inline-block shadow-sm">
                        <!-- Giả lập QR Code -->
                        <div style="width:100px; height:100px; background: repeating-linear-gradient(45deg, #000, #000 10px, #fff 10px, #fff 20px); border-radius:4px; opacity:0.8;"></div>
                    </div>
                    <p class="font-monospace small mt-3 mb-0 text-light px-3 py-1 bg-dark rounded border border-secondary">
                        <?= htmlspecialchars($transactionId) ?>
                    </p>
                </div>

            </div>
        </div>

        <div class="d-flex gap-3 justify-content-center mt-5">
            <a href="/my-tickets" class="btn btn-warning fw-bold px-4 btn-lg shadow">
                <i class="bi bi-ticket-perforated me-2"></i>Vé của tôi
            </a>
            <a href="/" class="btn btn-outline-secondary fw-semibold px-4 btn-lg text-light">
                Về trang chủ
            </a>
        </div>
    </div>
</div>

<!-- Confetti Effect -->
<div class="confetti-container" id="confetti-container"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Basic Confetti effect
    const container = document.getElementById('confetti-container');
    const colors = ['#ffc107', '#198754', '#dc3545', '#0dcaf0', '#d63384'];
    
    for (let i = 0; i < 50; i++) {
        const piece = document.createElement('div');
        piece.className = 'confetti-piece';
        piece.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        piece.style.left = Math.random() * 100 + 'vw';
        piece.style.animationDelay = Math.random() * 2 + 's';
        piece.style.animationDuration = Math.random() * 2 + 2 + 's';
        container.appendChild(piece);
    }
});
</script>
