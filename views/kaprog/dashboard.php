<div class="page-header">
    <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
        <span class="badge badge-primary">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                <polyline points="2 17 12 22 22 17"></polyline>
                <polyline points="2 12 12 17 22 12"></polyline>
            </svg>
            Kepala Konsentrasi Keahlian
        </span>
        <span class="badge badge-info">
            <?= e($jurusan['nama_jurusan']) ?> (<?= e($jurusan['kode_jurusan']) ?>)
        </span>
    </div>
    <h1 class="page-title">Dashboard Kepala Program Keahlian</h1>
    <p class="page-subtitle">Cakupan Pengelolaan Khusus Rumpun <?= e($jurusan['nama_jurusan']) ?> SMKN 9 Kabupaten Tangerang</p>
</div>

<!-- Metrik Khusus Jurusan Kaprog -->
<div class="grid-3" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= count($daftarKelas) ?> Kelas</h3>
            <p>Kelas Jurusan <?= e($jurusan['kode_jurusan']) ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon accent">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= number_format($totalSiswa) ?> Siswa</h3>
            <p>Total Siswa Jurusan Aktif</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= count($jadwalJurusan) ?> Sesi KBM</h3>
            <p>Jadwal Kejuruan Terdaftar</p>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 2rem;">
    <!-- Preview Kelas Jurusan -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-primary);">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                Kelas Jurusan <?= e($jurusan['kode_jurusan']) ?>
            </h3>
            <a href="<?= BASE_URL ?>/kaprog/kelas" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table table-compact">
                    <thead>
                        <tr>
                            <th style="width: 28%;">Tingkat</th>
                            <th style="width: 44%;">Nama Kelas</th>
                            <th style="width: 28%; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftarKelas as $k): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-neutral">Tkt <?= e($k['tingkat']) ?></span>
                                </td>
                                <td style="font-weight: 700; color: var(--color-primary);" title="<?= e($k['nama_kelas']) ?>"><?= e($k['nama_kelas']) ?></td>
                                <td style="text-align: center;">
                                    <span class="badge badge-success">Aktif</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Preview Jadwal KBM Kejuruan -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-accent);">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Jadwal KBM Kejuruan
            </h3>
            <a href="<?= BASE_URL ?>/kaprog/jadwal" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($jadwalJurusan)): ?>
                <div style="padding: 2.5rem 1.5rem; text-align: center; color: var(--color-text-muted);">
                    <p style="font-size: 13px;">Belum ada jadwal KBM yang terdaftar.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table table-compact">
                        <thead>
                            <tr>
                                <th style="width: 24%;">Hari & Jam</th>
                                <th style="width: 18%;">Kelas</th>
                                <th style="width: 38%;">Mapel & Guru</th>
                                <th class="col-hide-sm" style="width: 20%;">Ruangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jadwalJurusan as $jdw): ?>
                                <tr>
                                    <td>
                                        <div class="cell-stack">
                                            <div style="font-weight: 700; color: var(--color-text-main);"><?= e($jdw['hari']) ?></div>
                                            <div style="color: var(--color-text-muted); font-family: monospace;">
                                                <?= \App\Helpers\Sanitizer::formatJam($jdw['jam_mulai']) ?>-<?= \App\Helpers\Sanitizer::formatJam($jdw['jam_selesai']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-primary" title="<?= e($jdw['nama_kelas']) ?>"><?= e($jdw['nama_kelas']) ?></span></td>
                                    <td>
                                        <div class="cell-stack">
                                            <div style="font-weight: 700; color: var(--color-text-main);" title="<?= e($jdw['nama_mapel']) ?>"><?= e($jdw['nama_mapel']) ?></div>
                                            <div style="color: var(--color-secondary);" title="<?= e($jdw['nama_guru']) ?>"><?= e($jdw['nama_guru']) ?></div>
                                        </div>
                                    </td>
                                    <td class="col-hide-sm">
                                        <span class="badge badge-neutral" title="<?= e($jdw['ruangan'] ?? 'Ruang Teori') ?>"><?= e($jdw['ruangan'] ?? 'Ruang Teori') ?></span>
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
    <!-- Preview Siswa Jurusan -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-secondary);">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
                Siswa Jurusan <?= e($jurusan['kode_jurusan']) ?>
            </h3>
            <a href="<?= BASE_URL ?>/kaprog/siswa" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table table-compact">
                    <thead>
                        <tr>
                            <th class="col-hide-sm" style="width: 28%;">NIS</th>
                            <th style="width: 48%;">Nama Siswa</th>
                            <th style="width: 24%;">Kelas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewSiswa as $s): ?>
                            <tr>
                                <td class="col-hide-sm" style="font-family: monospace; font-weight: 700; color: var(--color-primary);" title="<?= e($s['nis']) ?>">
                                    <?= e($s['nis']) ?>
                                </td>
                                <td style="font-weight: 700; color: var(--color-text-main);" title="<?= e($s['nama_lengkap']) ?>"><?= e($s['nama_lengkap']) ?></td>
                                <td>
                                    <span class="badge badge-info" title="<?= e($s['nama_kelas'] ?? '-') ?>"><?= e($s['nama_kelas'] ?? '-') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Preview Guru Jurusan -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-primary);">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Guru & Wali Kelas Jurusan
            </h3>
            <a href="<?= BASE_URL ?>/kaprog/guru" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table table-compact">
                    <thead>
                        <tr>
                            <th class="col-hide-sm" style="width: 26%;">NUPTK</th>
                            <th style="width: 46%;">Nama Guru</th>
                            <th style="width: 28%;">Mapel</th>
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
                                        <div style="color: var(--color-primary);" title="<?= e($g['nama_peran'] ?? 'Guru') ?>"><?= e($g['nama_peran'] ?? 'Guru') ?></div>
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
</div>
