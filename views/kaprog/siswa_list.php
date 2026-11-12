<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/kaprog/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Siswa Jurusan <?= e($jurusan['kode_jurusan']) ?></span>
        </div>
        <h1 class="page-title">Data Siswa Jurusan <?= e($jurusan['nama_jurusan']) ?></h1>
        <p class="page-subtitle">Daftar siswa aktif pada kompetensi keahlian <?= e($jurusan['nama_jurusan']) ?> SMKN 9 Kabupaten Tangerang</p>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div style="position: relative; flex: 1; max-width: 360px;">
            <input type="text" id="cariSiswa" class="form-control" placeholder="Cari nama siswa, NIS..." style="font-size: 13px;" onkeyup="filterSiswa()">
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <select class="form-control" style="font-size: 13px; width: auto;" onchange="location.href='<?= BASE_URL ?>/kaprog/siswa?kelas=' + this.value">
                <option value="">Semua Kelas</option>
                <?php foreach ($daftarKelas as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= ($filterKelas == $k['id']) ? 'selected' : '' ?>><?= e($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>
            <span class="badge badge-neutral">Total: <?= count($daftarSiswa) ?> Siswa</span>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="data-table" id="tabelSiswa">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>NIS / NISN</th>
                        <th>Nama Lengkap Siswa</th>
                        <th>No. Ortu / WA</th>
                        <th>Kelas</th>
                        <th style="text-align: center;">Jenis Kelamin</th>
                        <th style="text-align: center;">Tahun Masuk</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarSiswa)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                                Tidak ada data siswa pada filter ini.
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
                                    <div style="font-size: 11px; color: var(--color-text-muted);"><?= e($s['alamat'] ?? 'Kabupaten Tangerang') ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; font-family: monospace; font-size: 12.5px; color: var(--color-text-main);">
                                        <?= e($s['telepon_orang_tua'] ?: '-') ?>
                                    </div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);">
                                        Siswa: <?= e($s['nomor_telepon'] ?: '-') ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info" style="font-weight: 700;"><?= e($s['nama_kelas'] ?? '-') ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <?= $s['jenis_kelamin'] === 'L' ? '♂ Laki-laki' : '♀ Perempuan' ?>
                                </td>
                                <td style="text-align: center; font-weight: 600; font-size: 13px;"><?= e($s['tahun_masuk']) ?></td>
                                <td style="text-align: center;">
                                    <?= \App\Helpers\Sanitizer::badgeStatus($s['status_siswa']) ?>
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

