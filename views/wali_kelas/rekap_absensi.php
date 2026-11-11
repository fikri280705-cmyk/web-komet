<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/wali-kelas/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Presensi Siswa</span>
        </div>
        <h1 class="page-title">Rekap Absensi Siswa - Kelas <?= e($kelas['nama_kelas']) ?></h1>
        <p class="page-subtitle">Pencatatan dan akumulasi presensi kehadiran siswa (Sakit, Izin, Tanpa Keterangan/Alfa) untuk buku rapor</p>
    </div>
</div>

<?php
$jumlahBelumDirekap = 0;
foreach ($rekapAbsensi as $rekap) {
    if ($rekap['id_rekap'] === null) {
        $jumlahBelumDirekap++;
    }
}
?>

<div class="card">
    <div class="card-header">
        <h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-warning);">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            Data Akumulasi Kehadiran &bull; Kelas <?= e($kelas['nama_kelas']) ?>
        </h3>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <span class="badge badge-neutral"><?= count($rekapAbsensi) ?> Siswa</span>
            <span class="badge <?= $jumlahBelumDirekap > 0 ? 'badge-warning' : 'badge-success' ?>">
                <?= $jumlahBelumDirekap ?> Belum Direkap
            </span>
        </div>
    </div>

    <?php if ($jumlahBelumDirekap > 0): ?>
        <form id="formAbsensiAwal" action="<?= BASE_URL ?>/wali-kelas/absensi/simpan" method="POST">
            <?= \App\Helpers\Session::csrfField() ?>
        </form>
    <?php endif; ?>

    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th style="width: 100px; text-align: center;">Sakit (Hari)</th>
                        <th style="width: 100px; text-align: center;">Izin (Hari)</th>
                        <th style="width: 100px; text-align: center;">Alfa (Hari)</th>
                        <th>Catatan Kehadiran / Wali Kelas</th>
                        <th style="min-width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rekapAbsensi)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                                Belum ada data siswa di kelas ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $idx = 1; foreach ($rekapAbsensi as $ab): ?>
                            <?php
                            $sudahDirekap = $ab['id_rekap'] !== null;
                            $idSiswa = (int)$ab['id_siswa'];
                            $formEditId = 'formEditAbsensi' . $idSiswa;
                            ?>
                            <tr>
                                <td style="text-align: center; color: var(--color-text-muted); font-weight: 600;">
                                    <?= $idx++ ?>
                                    <?php if ($sudahDirekap): ?>
                                        <form id="<?= e($formEditId) ?>" action="<?= BASE_URL ?>/wali-kelas/absensi/perbarui" method="POST">
                                            <?= \App\Helpers\Session::csrfField() ?>
                                            <input type="hidden" name="id_siswa" value="<?= $idSiswa ?>">
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td style="font-family: monospace; font-weight: 700; color: var(--color-primary); font-size: 13px;">
                                    <?= e($ab['nis']) ?>
                                </td>
                                <td style="font-weight: 700; color: var(--color-text-main);">
                                    <?= e($ab['nama_lengkap']) ?>
                                    <?php if ($sudahDirekap): ?>
                                        <div style="font-size: 11px; color: var(--color-success);">Rekap tersimpan &bull; terkunci</div>
                                    <?php else: ?>
                                        <div style="font-size: 11px; color: var(--color-warning);">Menunggu rekap awal</div>
                                    <?php endif; ?>
                                </td>
                                <?php foreach (['sakit', 'izin', 'alfa'] as $jenis): ?>
                                    <td>
                                        <input
                                            type="number"
                                            min="0"
                                            max="100"
                                            form="<?= $sudahDirekap ? e($formEditId) : 'formAbsensiAwal' ?>"
                                            name="<?= $sudahDirekap ? e($jenis) : 'absen[' . $idSiswa . '][' . e($jenis) . ']' ?>"
                                            class="form-control <?= $sudahDirekap ? 'input-absensi' : '' ?>"
                                            style="text-align: center; font-size: 13px; font-weight: 600;<?= $jenis === 'alfa' ? ' color: var(--color-danger);' : '' ?>"
                                            value="<?= $ab[$jenis] !== null ? (int)$ab[$jenis] : '' ?>"
                                            required
                                            <?= $sudahDirekap ? 'disabled' : '' ?>
                                        >
                                    </td>
                                <?php endforeach; ?>
                                <td>
                                    <input
                                        type="text"
                                        form="<?= $sudahDirekap ? e($formEditId) : 'formAbsensiAwal' ?>"
                                        name="<?= $sudahDirekap ? 'catatan' : 'absen[' . $idSiswa . '][catatan]' ?>"
                                        class="form-control <?= $sudahDirekap ? 'input-absensi' : '' ?>"
                                        style="font-size: 12.5px;"
                                        value="<?= e($ab['catatan'] ?? '') ?>"
                                        maxlength="255"
                                        placeholder="Catatan motivasi atau kehadiran..."
                                        <?= $sudahDirekap ? 'disabled' : '' ?>
                                    >
                                </td>
                                <td>
                                    <?php if ($sudahDirekap): ?>
                                        <button type="button" form="<?= e($formEditId) ?>" class="btn btn-outline btn-sm tombol-edit-absensi" onclick="mulaiEditAbsensi('<?= e($formEditId) ?>')">
                                            Ubah Data Siswa Ini
                                        </button>
                                        <div class="aksi-edit-absensi" style="display: none; gap: 0.35rem; flex-wrap: wrap;">
                                            <button type="submit" form="<?= e($formEditId) ?>" class="btn btn-primary btn-sm">Simpan Perubahan Siswa Ini</button>
                                            <button type="button" form="<?= e($formEditId) ?>" class="btn btn-outline btn-sm" onclick="batalkanEditAbsensi('<?= e($formEditId) ?>')">Batal</button>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">Diisi melalui submit kelas</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($jumlahBelumDirekap > 0): ?>
        <div style="padding: 1.25rem 1.5rem; background: var(--color-bg-subtle, #f8fafc); border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end;">
            <button type="submit" form="formAbsensiAwal" class="btn btn-primary btn-lg" style="display: inline-flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 7 21 7 13 17 13"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <span>Simpan Rekap Awal Seluruh Kelas</span>
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
function mulaiEditAbsensi(formId) {
    const form = document.getElementById(formId);
    const row = form.closest('tr');
    row.querySelectorAll('.input-absensi').forEach(input => {
        input.disabled = false;
    });
    row.querySelector('.tombol-edit-absensi').style.display = 'none';
    row.querySelector('.aksi-edit-absensi').style.display = 'flex';
}

function batalkanEditAbsensi(formId) {
    const form = document.getElementById(formId);
    const row = form.closest('tr');
    form.reset();
    row.querySelectorAll('.input-absensi').forEach(input => {
        input.disabled = true;
    });
    row.querySelector('.tombol-edit-absensi').style.display = '';
    row.querySelector('.aksi-edit-absensi').style.display = 'none';
}
</script>
