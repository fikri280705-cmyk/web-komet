<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/osis/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Kepengurusan OSIS</span>
        </div>
        <h1 class="page-title">Struktur Pengurus OSIS</h1>
        <p class="page-subtitle">Bagan dan susunan pengurus Organisasi Siswa Intra Sekolah SMKN 9 Kabupaten Tangerang Periode 2026/2027</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-primary);">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            Susunan Pengurus Inti & Sekbid OSIS
        </h3>
        <span class="badge badge-neutral">Total: <?= count($daftarPengurus) ?> Pengurus</span>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Jabatan Kepengurusan</th>
                        <th>Nama Pengurus & NIS</th>
                        <th>Kelas</th>
                        <th>Konsentrasi Keahlian</th>
                        <th style="text-align: center;">Periode</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarPengurus)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                                Belum ada data susunan pengurus OSIS.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $idx = 1; foreach ($daftarPengurus as $po): ?>
                            <tr>
                                <td style="text-align: center; color: var(--color-text-muted); font-weight: 600;"><?= $idx++ ?></td>
                                <td>
                                    <?php if (strpos($po['jabatan'], 'Ketua') !== false): ?>
                                        <span class="badge badge-primary" style="font-size: 13px; font-weight: 800;"><?= e($po['jabatan']) ?></span>
                                    <?php elseif (strpos($po['jabatan'], 'Sekretaris') !== false || strpos($po['jabatan'], 'Bendahara') !== false): ?>
                                        <span class="badge badge-info" style="font-weight: 700;"><?= e($po['jabatan']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral" style="font-weight: 600;"><?= e($po['jabatan']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-text-main); font-size: 13.5px;"><?= e($po['nama_lengkap']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted); font-family: monospace;">NIS: <?= e($po['nis']) ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-success" style="font-weight: 700;"><?= e($po['nama_kelas'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-primary"><?= e($po['kode_jurusan']) ?></span>
                                    <span style="font-size: 12px; margin-left: 4px; color: var(--color-text-body);"><?= e($po['nama_jurusan']) ?></span>
                                </td>
                                <td style="text-align: center; font-weight: 600; font-size: 13px;">
                                    <?= e($po['periode']) ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-success">
                                        <span class="badge-dot"></span> Aktif
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

