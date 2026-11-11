<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <span class="badge badge-primary">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                Organisasi Siswa Intra Sekolah (OSIS)
            </span>
            <span class="badge badge-neutral">Mading Digital</span>
        </div>
        <h1 class="page-title">Pusat Publikasi Event & Mading OSIS</h1>
        <p class="page-subtitle">Kelola usulan kegiatan kesiswaan, unggah materi poster, dan pantau proses verifikasi SMKN 9 Kabupaten Tangerang</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?= BASE_URL ?>/osis/event/baru" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Buat Usulan Event</span>
        </a>
    </div>
</div>

<!-- 4 Status Pipeline Cards -->
<div class="grid-4" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon neutral">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= $totalDraf ?></h3>
            <p>Draf Kegiatan</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= $totalPending ?></h3>
            <p>Menunggu Review</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon primary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= $totalPublik ?></h3>
            <p>Telah Dipublikasikan</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="21 8 21 21 3 21 3 8"></polyline>
                <rect x="1" y="3" width="22" height="5"></rect>
                <line x1="10" y1="12" x2="14" y2="12"></line>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= $totalArsip ?></h3>
            <p>Diarsipkan</p>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 2rem;">
    <!-- Preview Daftar Usulan Event -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-primary);">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                </svg>
                Kelola Event & Mading
            </h3>
            <a href="<?= BASE_URL ?>/osis/event" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($eventSaya)): ?>
                <div style="padding: 2.5rem 1.5rem; text-align: center; color: var(--color-text-muted);">
                    <p style="font-size: 13px;">Belum ada usulan event yang diajukan.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table table-compact">
                        <thead>
                            <tr>
                                <th style="width: 48%;">Judul</th>
                                <th class="col-hide-sm" style="width: 28%;">Kategori</th>
                                <th style="width: 24%; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($eventSaya, 0, 5) as $evt): ?>
                                <tr>
                                    <td>
                                        <div class="cell-stack">
                                            <div style="font-weight: 700; color: var(--color-text-main);" title="<?= e($evt['judul']) ?>"><?= e($evt['judul']) ?></div>
                                            <div style="color: var(--color-text-muted);" title="<?= e($evt['lokasi']) ?>"><?= e($evt['lokasi']) ?></div>
                                        </div>
                                    </td>
                                    <td class="col-hide-sm"><span class="badge badge-info" title="<?= e($evt['nama_kategori']) ?>"><?= e($evt['nama_kategori']) ?></span></td>
                                    <td style="text-align: center;"><?= \App\Helpers\Sanitizer::badgeStatus($evt['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Preview Struktur Pengurus OSIS -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-secondary);">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
                Struktur Pengurus OSIS
            </h3>
            <a href="<?= BASE_URL ?>/osis/pengurus" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table table-compact">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Jabatan</th>
                            <th style="width: 48%;">Nama Siswa</th>
                            <th style="width: 22%;">Kelas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pengurusOsis as $po): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-primary" style="font-weight: 700;" title="<?= e($po['jabatan']) ?>"><?= e($po['jabatan']) ?></span>
                                </td>
                                <td>
                                    <div class="cell-stack">
                                        <div style="font-weight: 700; color: var(--color-text-main);" title="<?= e($po['nama_lengkap']) ?>"><?= e($po['nama_lengkap']) ?></div>
                                        <div style="color: var(--color-text-muted);">NIS: <?= e($po['nis']) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-neutral" title="<?= e($po['nama_kelas'] ?? '-') ?>"><?= e($po['nama_kelas'] ?? '-') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
