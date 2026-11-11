<div class="page-header">
    <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
        <span class="badge badge-success">
            <span class="badge-dot"></span>
            Wali Kelas: <?= e($kelas['nama_kelas']) ?>
        </span>
        <span class="badge badge-neutral">Tingkat <?= e($kelas['tingkat']) ?></span>
    </div>
    <h1 class="page-title">Dashboard Wali Kelas</h1>
    <p class="page-subtitle">Pembinaan Akademik & Rapor Siswa Kelas <?= e($kelas['nama_kelas']) ?> (<?= e($kelas['nama_jurusan']) ?>) SMKN 9 Kabupaten Tangerang</p>
</div>

<!-- Metrik Khusus Kelas Wali Kelas -->
<div class="grid-3" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= number_format($totalSiswa) ?> Siswa</h3>
            <p>Siswa Terdaftar di <?= e($kelas['nama_kelas']) ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon accent">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= count($jadwalKelas) ?> Sesi</h3>
            <p>Jadwal Belajar Mingguan</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
        </div>
        <div class="stat-info">
            <h3 style="font-size: 1.35rem;">Rapor Digital</h3>
            <p>Kurikulum Merdeka SMK</p>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 2rem;">
    <!-- Preview Siswa Binaan -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-primary);">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
                Siswa Kelas <?= e($kelas['nama_kelas']) ?>
            </h3>
            <a href="<?= BASE_URL ?>/wali-kelas/siswa" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Jenis Kelamin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewSiswa as $s): ?>
                            <tr>
                                <td style="font-family: monospace; font-weight: 700; color: var(--color-primary); font-size: 13px;">
                                    <?= e($s['nis']) ?>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-text-main);"><?= e($s['nama_lengkap']) ?></div>
                                </td>
                                <td>
                                    <?= $s['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Preview Jadwal Pelajaran -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-accent);">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                Jadwal KBM Kelas
            </h3>
            <a href="<?= BASE_URL ?>/wali-kelas/jadwal" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($jadwalKelas)): ?>
                <div style="padding: 2.5rem 1.5rem; text-align: center; color: var(--color-text-muted);">
                    <p style="font-size: 13px;">Belum ada jadwal KBM yang terdaftar.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Hari & Jam</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengajar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jadwalKelas as $jdw): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--color-text-main);"><?= e($jdw['hari']) ?></div>
                                        <div style="font-size: 11px; color: var(--color-text-muted); font-family: monospace;">
                                            <?= \App\Helpers\Sanitizer::formatJam($jdw['jam_mulai']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--color-text-main);"><?= e($jdw['nama_mapel']) ?></div>
                                        <div style="font-size: 11px; color: var(--color-accent);">
                                            📍 <?= e($jdw['ruangan']) ?>
                                        </div>
                                    </td>
                                    <td style="color: var(--color-secondary); font-size: 12.5px; font-weight: 500;">
                                        <?= e($jdw['nama_guru']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Preview Input Rapor Nilai -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-success);">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                Input Rapor Nilai
            </h3>
            <a href="<?= BASE_URL ?>/wali-kelas/nilai" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body">
            <p style="font-size: 13px; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 1rem;">
                Kelola komponen nilai tugas, UTS, UAS, dan praktikum kejuruan siswa untuk menghasilkan nilai akhir dan predikat rapor digital.
            </p>
            <a href="<?= BASE_URL ?>/wali-kelas/nilai" class="btn btn-primary btn-block" style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                <span>Masuk ke Modul Input Rapor Nilai &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Preview Rekap Absensi Siswa -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-warning);">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                Rekap Absensi Siswa
            </h3>
            <a href="<?= BASE_URL ?>/wali-kelas/absensi" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th style="text-align: center;">Sakit</th>
                            <th style="text-align: center;">Izin</th>
                            <th style="text-align: center;">Alfa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewAbsen as $ab): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-text-main);"><?= e($ab['nama_lengkap']) ?></div>
                                </td>
                                <td style="text-align: center; font-weight: 600;"><?= (int)$ab['sakit'] ?></td>
                                <td style="text-align: center; font-weight: 600;"><?= (int)$ab['izin'] ?></td>
                                <td style="text-align: center; font-weight: 600;"><?= (int)$ab['alfa'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Preview Input Rapor Nilai -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-success);">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                Input Rapor Nilai
            </h3>
            <a href="<?= BASE_URL ?>/wali-kelas/nilai" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body">
            <p style="font-size: 13px; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 1rem;">
                Kelola komponen nilai tugas, UTS, UAS, dan praktikum kejuruan siswa untuk menghasilkan nilai akhir dan predikat rapor digital.
            </p>
            <a href="<?= BASE_URL ?>/wali-kelas/nilai" class="btn btn-primary btn-block" style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                <span>Masuk ke Modul Input Rapor Nilai &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Preview Rekap Absensi Siswa -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-warning);">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                Rekap Absensi Siswa
            </h3>
            <a href="<?= BASE_URL ?>/wali-kelas/absensi" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th style="text-align: center;">Sakit</th>
                            <th style="text-align: center;">Izin</th>
                            <th style="text-align: center;">Alfa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewAbsen as $ab): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-text-main);"><?= e($ab['nama_lengkap']) ?></div>
                                </td>
                                <td style="text-align: center; font-weight: 600;"><?= (int)$ab['sakit'] ?></td>
                                <td style="text-align: center; font-weight: 600;"><?= (int)$ab['izin'] ?></td>
                                <td style="text-align: center; font-weight: 600;"><?= (int)$ab['alfa'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

