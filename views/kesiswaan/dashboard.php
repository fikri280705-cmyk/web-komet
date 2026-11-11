<div class="page-header">
    <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
        <span class="badge badge-primary">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            Bidang Kesiswaan & Kurikulum
        </span>
    </div>
    <h1 class="page-title">Dashboard Kesiswaan</h1>
    <p class="page-subtitle">Selamat datang kembali, <?= e($user['nama_lengkap']) ?> &bull; Panel Manajemen Akademik & Pengawasan Sekolah SMKN 9 Kabupaten Tangerang</p>
</div>

<!-- 4 KPI Stat Cards -->
<div class="grid-4" style="margin-bottom: 2rem;">
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
            <h3><?= number_format($totalSiswa) ?></h3>
            <p>Total Siswa Aktif</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= number_format($totalGuru) ?></h3>
            <p>Tenaga Pendidik / Guru</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon accent">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= number_format($totalKelas) ?></h3>
            <p>Kelas</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= count($eventPending) ?></h3>
            <p>Usulan Menunggu Review</p>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 2rem;">
    <!-- Preview Moderasi Event -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-warning);">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Moderasi Event & Kegiatan OSIS
            </h3>
            <a href="<?= BASE_URL ?>/kesiswaan/moderasi-event" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($eventPending)): ?>
                <div style="padding: 2.5rem 1.5rem; text-align: center; color: var(--color-text-muted);">
                    <div style="width: 48px; height: 48px; margin: 0 auto 0.75rem; border-radius: 50%; background: #ECFDF5; color: #10B981; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div style="font-weight: 700; color: var(--color-text-main); font-size: 13.5px;">Semua Usulan Selesai Direview</div>
                    <p style="font-size: 12px; margin-top: 0.25rem;">Tidak ada antrean usulan event OSIS saat ini.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table table-compact">
                        <thead>
                            <tr>
                                <th style="width: 42%;">Judul</th>
                                <th style="width: 28%;">Pengusul</th>
                                <th class="col-hide-sm" style="width: 18%;">Kategori</th>
                                <th style="width: 12%; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventPending as $evt): ?>
                                <tr>
                                    <td>
                                        <div class="cell-stack">
                                            <div style="font-weight: 700; color: var(--color-text-main);" title="<?= e($evt['judul']) ?>"><?= e($evt['judul']) ?></div>
                                            <div style="font-size: 10.5px; color: var(--color-text-muted); margin-top: 2px;" title="<?= e($evt['lokasi']) ?>"><?= e($evt['lokasi']) ?></div>
                                        </div>
                                    </td>
                                    <td title="<?= e($evt['nama_pengusul']) ?>"><?= e($evt['nama_pengusul']) ?></td>
                                    <td class="col-hide-sm"><span class="badge badge-info" title="<?= e($evt['nama_kategori']) ?>"><?= e($evt['nama_kategori']) ?></span></td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-warning">Review</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Preview Konsentrasi Keahlian (Jurusan) -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-primary);">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                    <polyline points="2 17 12 22 22 17"></polyline>
                    <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
                Konsentrasi Keahlian (Jurusan)
            </h3>
            <a href="<?= BASE_URL ?>/kesiswaan/jurusan" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table table-compact">
                    <thead>
                        <tr>
                            <th style="width: 16%;">Kode</th>
                            <th style="width: 48%;">Konsentrasi</th>
                            <th class="col-hide-sm" style="width: 36%;">Kaprog</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftarJurusan as $jur): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-primary" style="font-weight: 800;"><?= e($jur['kode_jurusan']) ?></span>
                                </td>
                                <td style="font-weight: 700; color: var(--color-text-main);" title="<?= e($jur['nama_jurusan']) ?>"><?= e($jur['nama_jurusan']) ?></td>
                                <td class="col-hide-sm" style="color: var(--color-secondary); font-weight: 600;" title="<?= e($jur['nama_kaprog'] ?? 'Belum ditentukan') ?>">
                                    <?= e($jur['nama_kaprog'] ?? 'Belum ditentukan') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Preview Data Guru & Pegawai -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-secondary);">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Tenaga Pendidik & Pegawai
            </h3>
            <a href="<?= BASE_URL ?>/kesiswaan/guru" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table table-compact">
                    <thead>
                        <tr>
                            <th class="col-hide-sm" style="width: 26%;">NUPTK</th>
                            <th style="width: 48%;">Nama Guru</th>
                            <th style="width: 26%;">Mapel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewGuru as $g): ?>
                            <tr>
                                <td class="col-hide-sm" style="font-family: monospace; font-weight: 600; color: var(--color-text-muted);" title="<?= e($g['nuptk']) ?>">
                                    <?= e($g['nuptk']) ?>
                                </td>
                                <td>
                                    <div class="cell-stack">
                                        <div style="font-weight: 700; color: var(--color-text-main);" title="<?= e($g['nama_lengkap']) ?>"><?= e($g['nama_lengkap']) ?></div>
                                        <div style="font-size: 10.5px; color: var(--color-primary);" title="<?= e($g['nama_peran'] ?? 'Guru Pengajar') ?>"><?= e($g['nama_peran'] ?? 'Guru Pengajar') ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-neutral" title="<?= e($g['mapel_utama'] ?? 'Umum') ?>"><?= e($g['mapel_utama'] ?? 'Umum') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Preview Data Induk Siswa -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-accent);">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
                Data Induk Siswa
            </h3>
            <a href="<?= BASE_URL ?>/kesiswaan/siswa" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table table-compact">
                    <thead>
                        <tr>
                            <th class="col-hide-sm" style="width: 22%;">NIS</th>
                            <th style="width: 46%;">Nama Siswa</th>
                            <th style="width: 20%;">Kelas</th>
                            <th style="width: 12%;">Jurusan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewSiswa as $s): ?>
                            <tr>
                                <td class="col-hide-sm" style="font-family: monospace; font-weight: 700; color: var(--color-primary);" title="<?= e($s['nis']) ?>">
                                    <?= e($s['nis']) ?>
                                </td>
                                <td>
                                    <div class="cell-stack">
                                        <div style="font-weight: 700; color: var(--color-text-main);" title="<?= e($s['nama_lengkap']) ?>"><?= e($s['nama_lengkap']) ?></div>
                                        <div style="font-size: 10.5px; color: var(--color-text-muted);"><?= $s['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info" title="<?= e($s['nama_kelas'] ?? '-') ?>"><?= e($s['nama_kelas'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-primary"><?= e($s['kode_jurusan'] ?? '-') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
