<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/kesiswaan/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Program Keahlian</span>
        </div>
        <h1 class="page-title">Konsentrasi Keahlian (Jurusan)</h1>
        <p class="page-subtitle">Daftar 6 Program Kejuruan Vokasi, Kepala Program Keahlian, dan Alokasi Laboratorium SMKN 9 Kabupaten Tangerang</p>
    </div>
</div>

<div class="grid-3" style="margin-bottom: 2rem;">
    <?php foreach ($daftarJurusan as $jur): ?>
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="card-header" style="padding-bottom: 0.75rem;">
                    <div>
                        <span class="badge badge-primary" style="font-size: 13px; font-weight: 800;"><?= e($jur['kode_jurusan']) ?></span>
                    </div>
                    <span class="badge badge-success">Aktif</span>
                </div>
                <div class="card-body">
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--color-text-main); margin-bottom: 0.75rem;"><?= e($jur['nama_jurusan']) ?></h3>
                    <p style="font-size: 12.5px; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 1.25rem;">
                        <?= e($jur['deskripsi'] ?? 'Program keahlian terakreditasi A dengan kurikulum berbasis industri.') ?>
                    </p>

                    <div style="background: var(--color-bg-subtle, #f8fafc); padding: 0.85rem; border-radius: var(--radius-md); border: 1px solid var(--color-border); margin-bottom: 1rem;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--color-text-muted); letter-spacing: 0.05em; margin-bottom: 4px;">Kepala Program Keahlian (Kaprog):</div>
                        <div style="font-size: 13.5px; font-weight: 700; color: var(--color-primary);"><?= e($jur['nama_kaprog'] ?? 'Belum Ditugaskan') ?></div>
                    </div>

                    <?php
                    // Hitung jumlah kelas pada jurusan ini
                    $kelasJurusan = array_filter($daftarKelas, function($k) use ($jur) {
                        return $k['id_jurusan'] == $jur['id'];
                    });
                    ?>

                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <span class="badge badge-neutral" style="font-size: 12px; font-weight: 600;">
                            <?= count($kelasJurusan) ?> Kelas (10, 11, 12)
                        </span>
                        <?php if (in_array($jur['kode_jurusan'], ['TKJ', 'DKV', 'AKC', 'FKK', 'TBSM'])): ?>
                            <span class="badge badge-info" style="font-size: 11.5px;">Tersedia Fasilitas Lab/Bengkel</span>
                        <?php else: ?>
                            <span class="badge badge-neutral" style="font-size: 11.5px;">Teori & Praktik Kantor</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--color-border); background: var(--color-bg-subtle, #f8fafc); text-align: right;">
                <a href="<?= BASE_URL ?>/kesiswaan/kelas?jurusan=<?= (int)$jur['id'] ?>" class="btn btn-primary btn-sm">Lihat Kelas &rarr;</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
