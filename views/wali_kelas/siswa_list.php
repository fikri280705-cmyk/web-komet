<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/wali-kelas/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Kelas <?= e($kelas['nama_kelas']) ?></span>
        </div>
        <h1 class="page-title">Siswa Kelas Binaan <?= e($kelas['nama_kelas']) ?></h1>
        <p class="page-subtitle">Daftar lengkap biodata, kontak, dan orang tua siswa binaan <?= e($kelas['nama_kelas']) ?> SMKN 9 Kabupaten Tangerang</p>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div style="position: relative; flex: 1; max-width: 360px;">
            <input type="text" id="cariSiswa" class="form-control" placeholder="Cari nama siswa, NIS..." style="font-size: 13px;" onkeyup="filterSiswa()">
        </div>
        <span class="badge badge-neutral">Total: <?= count($daftarSiswa) ?> Siswa Terdaftar</span>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="data-table" id="tabelSiswa">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>NIS / NISN</th>
                        <th>Nama Siswa & Tempat Lahir</th>
                        <th style="text-align: center;">L/P</th>
                        <th>Kontak & Orang Tua</th>
                        <th>Alamat Domisili</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarSiswa)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                                Belum ada siswa yang terdaftar di kelas ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $idx = 1; foreach ($daftarSiswa as $s): ?>
                            <tr class="baris-siswa">
                                <td style="text-align: center; color: var(--color-text-muted); font-weight: 600;"><?= $idx++ ?></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-primary); font-family: monospace; font-size: 13.5px;"><?= e($s['nis']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);">NISN: <?= e($s['nisn'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-text-main);"><?= e($s['nama_lengkap']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);">
                                        <?= e($s['tempat_lahir'] ?? 'Tangerang') ?>, <?= !empty($s['tanggal_lahir']) ? date('d M Y', strtotime($s['tanggal_lahir'])) : '-' ?>
                                    </div>
                                </td>
                                <td style="text-align: center; font-weight: 700;">
                                    <?= e($s['jenis_kelamin']) ?>
                                </td>
                                <td>
                                    <div style="font-size: 12.5px; font-weight: 600; color: var(--color-text-body);">
                                        Ortu: <?= e($s['nama_ayah'] ?? ($s['nama_ibu'] ?? '-')) ?>
                                    </div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);">
                                        Telp: <?= e($s['telepon_orang_tua'] ?? ($s['nomor_telepon'] ?? '-')) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 12px; color: var(--color-text-body); max-width: 220px; line-height: 1.4;">
                                        <?= e($s['alamat'] ?? 'Kabupaten Tangerang') ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <?= \App\Helpers\Sanitizer::badgeStatus($s['status_siswa'] ?? 'aktif') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterSiswa() {
    const filter = document.getElementById('cariSiswa').value.toLowerCase();
    const rows = document.querySelectorAll('#tabelSiswa .baris-siswa');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>

