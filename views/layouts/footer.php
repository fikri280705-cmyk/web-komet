    <footer class="app-footer">
        <div>&copy; <?= date('Y') ?> SMK Negeri 9 Kabupaten Tangerang. Seluruh Hak Cipta Dilindungi.</div>
        <div>SIAKAD SMKN 9 Kabupaten Tangerang</div>
    </footer>
</div><!-- .main-wrapper -->

<!-- Modal Konfirmasi Logout Modern & Rapi -->
<div id="modalLogout" class="modal-overlay" style="display: none;">
    <div class="modal-card">
        <div class="modal-icon-warning">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
        </div>
        <h3 class="modal-title">Konfirmasi Keluar Sistem</h3>
        <p class="modal-desc">Apakah Anda yakin ingin keluar dari sesi SIAKAD SMKN 9 Kabupaten Tangerang?</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" onclick="tutupModalLogout()">Batal</button>
            <a href="<?= BASE_URL ?>/logout" class="btn btn-danger">Ya, Keluar</a>
        </div>
    </div>
</div>

<?php require_once PATH_VIEWS . '/layouts/mobile_nav.php'; ?>

</div><!-- .app-container -->

<!-- JS Application -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<script>
function bukaModalLogout(e) {
    if (e) e.preventDefault();
    document.getElementById('modalLogout').style.display = 'flex';
}
function tutupModalLogout() {
    document.getElementById('modalLogout').style.display = 'none';
}
// Close on click outside modal
window.addEventListener('click', function(e) {
    const modal = document.getElementById('modalLogout');
    if (e.target === modal) {
        tutupModalLogout();
    }
});
</script>
</body>
</html>
