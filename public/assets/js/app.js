// public/assets/js/app.js

document.addEventListener('DOMContentLoaded', () => {

    // ── 3. Poster Phim Nghiêng 3D Theo Chuột ──────────────────────────────
    document.querySelectorAll('.movie-card-3d').forEach(card => {
        // Tìm element card bên trong
        const inner = card.querySelector('.movie-card');
        if (!inner) return;

        // Thêm class card-inner để có transition đúng chuẩn
        inner.classList.add('card-inner');

        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = (centerY - y) / 12;
            const rotateY = (x - centerX) / 12;

            inner.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });

        card.addEventListener('mouseleave', () => {
            inner.style.transform = 'rotateX(0) rotateY(0)';
        });
    });

});
