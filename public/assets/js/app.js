/**
 * Script Interaktivitas SIAKAD SMKN 9 Tangerang
 * Bebas library eksternal (Pure Vanilla JavaScript)
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Sidebar Drawer Toggle
    const hamburgerBtn = document.querySelector('.hamburger-btn');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    if (hamburgerBtn && sidebar && overlay) {
        hamburgerBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }

    // 2. Toggle Password Visibility (Show/Hide)
    const togglePassBtns = document.querySelectorAll('.toggle-password-btn');
    togglePassBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    btn.textContent = 'Sembunyikan';
                } else {
                    input.type = 'password';
                    btn.textContent = 'Lihat';
                }
            }
        });
    });

    // 3. Live Preview Upload Poster Event (OSIS)
    const posterInput = document.getElementById('input_foto_poster');
    const posterPreview = document.getElementById('preview_foto_poster');
    const previewContainer = document.getElementById('container_preview_poster');

    if (posterInput && posterPreview && previewContainer) {
        posterInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                // Validasi ukuran sisi klien
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran berkas terlalu besar! Maksimal adalah 2 MB.');
                    this.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    posterPreview.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
            }
        });
    }

    // 4. Auto-dismiss Flash Alerts setelah 4 detik
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 500);
            });
        }, 4000);
    }
});

