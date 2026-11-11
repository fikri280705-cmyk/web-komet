<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/wali-kelas/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Kelas <?= e($kelas['nama_kelas']) ?></span>
        </div>
        <h1 class="page-title">Input Rapor Nilai Siswa</h1>
        <p class="page-subtitle">Pengisian nilai tugas, asesmen tengah semester, akhir semester, dan uji praktikum kejuruan</p>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <label class="form-label" style="margin-bottom: 0; font-weight: 700;">Pilih Mata Pelajaran:</label>
            <select class="form-control" style="width: auto; min-width: 280px; font-size: 13px;" onchange="location.href='<?= BASE_URL ?>/wali-kelas/nilai?penugasan=' + this.value">
                <?php foreach ($daftarMapel as $mp): ?>
                    <option value="<?= (int)$mp['id_penugasan_guru'] ?>" <?= ($idPenugasan == $mp['id_penugasan_guru']) ? 'selected' : '' ?>>
                        <?= e($mp['nama_mapel']) ?> &bull; <?= e($mp['nama_guru']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="font-size: 12px; color: var(--color-text-muted);">
            Bobot SMK: <strong>Tugas 20% &bull; UTS 20% &bull; UAS 20% &bull; Praktik 40%</strong>
        </div>
    </div>

    <?php if (empty($daftarNilai)): ?>
        <div class="card-body" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
            <p>Pilih mata pelajaran yang valid untuk mengisi nilai siswa.</p>
        </div>
    <?php else: ?>
        <?php
        $jumlahBelumDinilai = 0;
        foreach ($daftarNilai as $barisNilai) {
            if ($barisNilai['id_nilai'] === null) {
                $jumlahBelumDinilai++;
            }
        }
        ?>
        <?php if ($jumlahBelumDinilai > 0): ?>
            <form id="formNilaiAwal" action="<?= BASE_URL ?>/wali-kelas/nilai/simpan" method="POST">
                <?= \App\Helpers\Session::csrfField() ?>
                <input type="hidden" name="id_penugasan" value="<?= (int)$idPenugasan ?>">
            </form>
        <?php endif; ?>

        <div class="card-header" style="padding-top: 0; flex-wrap: wrap;">
            <span class="badge badge-neutral"><?= count($daftarNilai) ?> Siswa</span>
            <span class="badge <?= $jumlahBelumDinilai > 0 ? 'badge-warning' : 'badge-success' ?>">
                <?= $jumlahBelumDinilai ?> Belum Dinilai
            </span>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th>NIS</th>
                            <th>Nama Lengkap Siswa</th>
                            <th style="width: 90px; text-align: center;">Tugas (20%)</th>
                            <th style="width: 90px; text-align: center;">UTS (20%)</th>
                            <th style="width: 90px; text-align: center;">UAS (20%)</th>
                            <th style="width: 95px; text-align: center;">Praktik (40%)</th>
                            <th style="width: 85px; text-align: center;">Nilai Akhir</th>
                            <th style="width: 75px; text-align: center;">Predikat</th>
                            <th style="min-width: 185px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $idx = 1; foreach ($daftarNilai as $n): ?>
                            <?php
                            $sudahDinilai = $n['id_nilai'] !== null;
                            $idSiswa = (int)$n['id_siswa'];
                            $formEditId = 'formEditNilai' . $idSiswa;
                            ?>
                            <tr>
                                <td style="text-align: center; color: var(--color-text-muted); font-weight: 600;">
                                    <?= $idx++ ?>
                                    <?php if ($sudahDinilai): ?>
                                        <form id="<?= e($formEditId) ?>" action="<?= BASE_URL ?>/wali-kelas/nilai/perbarui" method="POST">
                                            <?= \App\Helpers\Session::csrfField() ?>
                                            <input type="hidden" name="id_siswa" value="<?= $idSiswa ?>">
                                            <input type="hidden" name="id_penugasan" value="<?= (int)$idPenugasan ?>">
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td style="font-family: monospace; font-weight: 700; color: var(--color-primary); font-size: 13px;">
                                    <?= e($n['nis']) ?>
                                </td>
                                <td style="font-weight: 700; color: var(--color-text-main);">
                                    <?= e($n['nama_lengkap']) ?>
                                    <?php if ($sudahDinilai): ?>
                                        <div style="font-size: 11px; color: var(--color-success);">Nilai tersimpan &bull; terkunci</div>
                                    <?php else: ?>
                                        <div style="font-size: 11px; color: var(--color-warning);">Menunggu input nilai</div>
                                    <?php endif; ?>
                                </td>
                                <?php foreach (['tugas' => 'nilai_tugas', 'uts' => 'nilai_uts', 'uas' => 'nilai_uas', 'praktik' => 'nilai_praktik'] as $komponen => $kolomNilai): ?>
                                    <td>
                                        <input
                                            type="number"
                                            step="0.1"
                                            min="0"
                                            max="100"
                                            form="<?= $sudahDinilai ? e($formEditId) : 'formNilaiAwal' ?>"
                                            name="<?= $sudahDinilai ? e($komponen) : 'nilai[' . $idSiswa . '][' . e($komponen) . ']' ?>"
                                            class="form-control <?= $sudahDinilai ? 'input-nilai' : '' ?>"
                                            style="text-align: center; font-size: 13px; padding: 4px 6px;"
                                            value="<?= $n[$kolomNilai] ?? '' ?>"
                                            placeholder="0-100"
                                            <?= $sudahDinilai ? 'disabled' : '' ?>
                                        >
                                    </td>
                                <?php endforeach; ?>
                                <td style="text-align: center; font-weight: 800; font-size: 14px; color: var(--color-primary);">
                                    <?= $n['nilai_akhir'] !== null ? number_format((float)$n['nilai_akhir'], 1) : '-' ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($n['predikat_huruf'])): ?>
                                        <span class="badge <?= in_array($n['predikat_huruf'], ['A', 'B'], true) ? 'badge-success' : 'badge-warning' ?>" style="font-weight: 800; font-size: 13px;">
                                            <?= e($n['predikat_huruf']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--color-text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($sudahDinilai): ?>
                                        <button type="button" class="btn btn-outline btn-sm tombol-edit-nilai" onclick="mulaiEditNilai('<?= e($formEditId) ?>')">
                                            Ubah Nilai Siswa Ini
                                        </button>
                                        <div class="aksi-edit-nilai" style="display: none; gap: 0.35rem; flex-wrap: wrap;">
                                            <button type="submit" form="<?= e($formEditId) ?>" class="btn btn-primary btn-sm">Simpan Perubahan Siswa Ini</button>
                                            <button type="button" class="btn btn-outline btn-sm" onclick="batalkanEditNilai('<?= e($formEditId) ?>')">Batal</button>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">Diisi melalui submit kelas</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($jumlahBelumDinilai > 0): ?>
            <div style="padding: 1.25rem 1.5rem; background: var(--color-bg-subtle, #f8fafc); border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end;">
                <button type="submit" form="formNilaiAwal" class="btn btn-primary btn-lg" style="display: inline-flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 7 21 7 13 17 13"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Nilai Awal Seluruh Kelas</span>
                </button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function mulaiEditNilai(formId) {
    const form = document.getElementById(formId);
    const row = form.closest('tr');
    row.querySelectorAll('.input-nilai').forEach(input => {
        input.dataset.nilaiAwal = input.value;
        input.disabled = false;
        input.required = true;
    });
    row.querySelector('.tombol-edit-nilai').style.display = 'none';
    row.querySelector('.aksi-edit-nilai').style.display = 'flex';
}

function batalkanEditNilai(formId) {
    const form = document.getElementById(formId);
    const row = form.closest('tr');
    row.querySelectorAll('.input-nilai').forEach(input => {
        input.value = input.dataset.nilaiAwal;
        input.disabled = true;
        input.required = false;
    });
    row.querySelector('.tombol-edit-nilai').style.display = '';
    row.querySelector('.aksi-edit-nilai').style.display = 'none';
}
</script>
