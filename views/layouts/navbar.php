<?php 
use App\Helpers\Session; 
$fotoProfil = Session::get('foto_profil', 'avatar_default.png');
$namaUser = Session::get('nama_lengkap', 'Pengguna');
$peranUser = Session::get('nama_peran', 'Sistem');
?>
<header class="top-navbar">
    <div class="navbar-left">
        <button type="button" class="hamburger-btn" aria-label="Buka Menu Navigasi">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <div class="academic-badge">
            <span class="badge-dot"></span>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-accent);">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span class="text"><?= \App\Helpers\Sanitizer::tahunAjaranRealtime() ?></span>
        </div>
    </div>

    <div class="navbar-right">
        <a href="<?= BASE_URL ?>/pengaturan-akun" class="user-profile-btn" title="Kelola Profil dan Pengaturan Akun">
            <div class="user-info-text">
                <div class="user-name"><?= e($namaUser) ?></div>
                <div class="user-role-pill"><?= e($peranUser) ?></div>
            </div>
            <?php if (!empty($fotoProfil) && $fotoProfil !== 'avatar_default.png' && file_exists(PATH_UPLOADS . '/profil/' . $fotoProfil)): ?>
                <img src="<?= BASE_URL ?>/uploads/profil/<?= e($fotoProfil) ?>" alt="Avatar" class="user-avatar-mini" style="width: 36px; height: 36px; object-fit: cover;">
            <?php else: ?>
                <div class="user-avatar-mini" style="width: 36px; height: 36px; font-size: 13.5px;">
                    <?= strtoupper(substr($namaUser, 0, 1)) ?>
                </div>
            <?php endif; ?>
        </a>
    </div>
</header>
