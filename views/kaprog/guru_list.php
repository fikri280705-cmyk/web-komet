<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/kaprog/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Tenaga Pengajar Kejuruan</span>
        </div>
        <h1 class="page-title">Guru & Wali Kelas Jurusan <?= e($jurusan['nama_jurusan']) ?></h1>
        <p class="page-subtitle">Daftar tenaga pendidik kejuruan dan penugasan wali kelas untuk rumpun <?= e($jurusan['kode_jurusan']) ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div style="position: relative; flex: 1; max-width: 360px;">
            <input type="text" id="cariGuru" class="form-control" placeholder="Cari nama guru, NUPTK..." style="font-size: 13px;" onkeyup="filterGuru()">
        </div>
        <span class="badge badge-neutral">Total: <?= count($daftarGuru) ?> Guru Terdaftar</span>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="data-table" id="tabelGuru">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>NUPTK / NIP</th>
                        <th>Nama Guru</th>
                        <th>Mata Pelajaran</th>
                        <th>Penugasan Saat Ini</th>
                        <th style="text-align: center; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarGuru)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                                Belum ada guru yang terdaftar di jurusan ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $idx = 1; foreach ($daftarGuru as $g): ?>
                            <tr class="baris-guru">
                                <td style="text-align: center; color: var(--color-text-muted); font-weight: 600;"><?= $idx++ ?></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-primary); font-family: monospace; font-size: 13px;"><?= e($g['nuptk']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);">NIP: <?= e($g['nip'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-text-main);"><?= e($g['nama_lengkap']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);"><?= $g['jenis_kelamin'] === 'L' ? '♂ Laki-laki' : '♀ Perempuan' ?></div>
                                </td>
                                <td>
                                    <span style="font-size: 13px; font-weight: 600; color: var(--color-text-body);"><?= e($g['mapel_utama'] ?? 'Produktif Kejuruan') ?></span>
                                </td>
                                <td>
                                    <?php if ($g['kode_peran'] === 'kaprog'): ?>
                                        <span class="badge badge-warning" style="font-weight: 700;">Kepala Program</span>
                                    <?php endif; ?>
                                    <?php if (!empty($g['nama_kelas_wali'])): ?>
                                        <span class="badge badge-success" style="font-weight: 700;">Wali Kelas: <?= e($g['nama_kelas_wali']) ?></span>
                                    <?php elseif ($g['kode_peran'] !== 'kaprog'): ?>
                                        <span class="badge badge-neutral">Guru Pengajar</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="bukaModalPlotWali(<?= (int)$g['id'] ?>, '<?= e(addslashes($g['nama_lengkap'])) ?>')" style="padding: 4px 8px; font-size: 11.5px;">
                                        Tugaskan Wali
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Plot Wali Kelas -->
<div id="modalPlotWali" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 440px;">
        <div class="modal-header">
            <h3 style="font-size: 15px; font-weight: 800; color: var(--color-text-main);">Tugaskan Sebagai Wali Kelas</h3>
            <button type="button" onclick="tutupModalPlotWali()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--color-text-muted);">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/kaprog/guru/wali-kelas" method="POST">
            <?= \App\Helpers\Session::csrfField() ?>
            <input type="hidden" id="waliIdGuru" name="id_guru" value="">
            <div class="modal-body">
                <p id="waliNamaGuru" style="font-weight: 700; font-size: 13.5px; margin-bottom: 1rem; color: var(--color-primary);"></p>
                <div class="form-group">
                    <label class="form-label">Pilih Kelas Jurusan <?= e($jurusan['kode_jurusan']) ?>:</label>
                    <select name="id_kelas" class="form-control" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($daftarKelas as $k): ?>
                            <option value="<?= (int)$k['id'] ?>"><?= e($k['nama_kelas']) ?> (Tingkat <?= e($k['tingkat']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="tutupModalPlotWali()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Penugasan</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterGuru() {
    const filter = document.getElementById('cariGuru').value.toLowerCase();
    const rows = document.querySelectorAll('#tabelGuru .baris-guru');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
function bukaModalPlotWali(idGuru, namaGuru) {
    document.getElementById('waliIdGuru').value = idGuru;
    document.getElementById('waliNamaGuru').textContent = 'Guru: ' + namaGuru;
    document.getElementById('modalPlotWali').style.display = 'flex';
}
function tutupModalPlotWali() {
    document.getElementById('modalPlotWali').style.display = 'none';
}
</script>
