<?php
use App\Helpers\Session;
$role = Session::role();
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <img src="<?= BASE_URL ?>/assets/images/logosmkn9.png" alt="Logo SMKN 9" class="sidebar-logo">
        <div class="sidebar-brand-text">
            <h2>SIAKAD SMKN 9</h2>
            <p>Kabupaten Tangerang</p>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="menu-category">Menu Utama</li>

        <?php if ($role === 'kesiswaan'): ?>
            <li class="menu-item <?= strpos($currentUri, '/kesiswaan/dashboard') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/kesiswaan/dashboard">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/kesiswaan/tahun-ajaran') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/kesiswaan/tahun-ajaran">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                            <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"></path>
                        </svg>
                    </span>
                    <span>Tahun Ajaran & Kenaikan</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/kesiswaan/siswa') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/kesiswaan/siswa">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </span>
                    <span>Data Induk Siswa</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/kesiswaan/guru') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/kesiswaan/guru">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
                    <span>Data Guru & Pegawai</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/kesiswaan/moderasi-event') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/kesiswaan/moderasi-event">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </span>
                    <span>Moderasi Event</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/kesiswaan/jurusan') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/kesiswaan/jurusan">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                    </span>
                    <span>Konsentrasi Keahlian</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/kesiswaan/jadwal') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/kesiswaan/jadwal">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </span>
                    <span>Jadwal KBM Semua Jurusan</span>
                </a>
            </li>

        <?php elseif ($role === 'kaprog'): ?>
            <li class="menu-item <?= strpos($currentUri, '/kaprog/dashboard') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/kaprog/dashboard">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/kaprog/siswa') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/kaprog/siswa">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                        </svg>
                    </span>
                    <span>Siswa Jurusan</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/kaprog/guru') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/kaprog/guru">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
                    <span>Guru & Wali Kelas</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/kaprog/kelas') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/kaprog/kelas">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </span>
                    <span>Kelas Jurusan</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/kaprog/jadwal') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/kaprog/jadwal">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </span>
                    <span>Jadwal KBM</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/kaprog/jadwal-lab') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/kaprog/jadwal-lab">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                    </span>
                    <span>Jadwal Lab & Bengkel</span>
                </a>
            </li>

        <?php elseif ($role === 'wali_kelas'): ?>
            <li class="menu-item <?= strpos($currentUri, '/wali-kelas/dashboard') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/wali-kelas/dashboard">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/wali-kelas/siswa') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/wali-kelas/siswa">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                        </svg>
                    </span>
                    <span>Siswa Kelas Binaan</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/wali-kelas/jadwal') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/wali-kelas/jadwal">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </span>
                    <span>Jadwal Pelajaran Kelas</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/wali-kelas/nilai') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/wali-kelas/nilai">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </span>
                    <span>Input Rapor Nilai</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/wali-kelas/absensi') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/wali-kelas/absensi">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11l3 3L22 4"></path>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                        </svg>
                    </span>
                    <span>Rekap Absensi Siswa</span>
                </a>
            </li>

        <?php elseif ($role === 'osis'): ?>
            <li class="menu-item <?= strpos($currentUri, '/osis/dashboard') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/osis/dashboard">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/osis/event') !== false && strpos($currentUri, '/event/baru') === false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/osis/event">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </span>
                    <span>Kelola Event & Mading</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/osis/event/baru') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/osis/event/baru">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                    </span>
                    <span>Buat Usulan Event</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/osis/pengurus') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/osis/pengurus">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </span>
                    <span>Struktur Pengurus OSIS</span>
                </a>
            </li>

        <?php elseif ($role === 'siswa'): ?>
            <li class="menu-item <?= strpos($currentUri, '/siswa/dashboard') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/siswa/dashboard">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </span>
                    <span>Beranda</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/siswa/jadwal') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/siswa/jadwal">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </span>
                    <span>Jadwal Belajar Saya</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/siswa/nilai') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/siswa/nilai">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </span>
                    <span>Rapor & Transkrip Nilai</span>
                </a>
            </li>
            <li class="menu-item <?= strpos($currentUri, '/siswa/event') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/siswa/event">
                    <span class="menu-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </span>
                    <span>Event & Mading Sekolah</span>
                </a>
            </li>
        <?php endif; ?>

        <li class="menu-category">Pengaturan & Sesi</li>
        <li class="menu-item <?= strpos($currentUri, '/pengaturan-akun') !== false || strpos($currentUri, '/profil/detail') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/pengaturan-akun">
                <span class="menu-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                </span>
                <span>Pengaturan Akun</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="javascript:void(0)" onclick="bukaModalLogout(event)">
                <span class="menu-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </span>
                <span>Keluar (Logout)</span>
            </a>
        </li>
    </ul>
</aside>
