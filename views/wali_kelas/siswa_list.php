<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/wali-kelas/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Kelas <?= e($kelas['nama_kelas']) ?></span>
            <span class="badge badge-info">Presensi <?= date('d M Y', strtotime($tanggalPresensi ?? 'now')) ?></span>
        </div>
        <h1 class="page-title">Siswa Kelas Binaan <?= e($kelas['nama_kelas']) ?></h1>
        <p class="page-subtitle">Pilih touchbar Hadir / Izin / Sakit / Tidak Hadir per siswa, lalu tekan Simpan. WhatsApp dikirim ke nomor orang tua saat status berubah.</p>
    </div>
</div>

<form method="POST" action="<?= BASE_URL ?>/wali-kelas/siswa/presensi" id="formPresensiKelas">
    <?= \App\Helpers\Session::csrfField() ?>

    <div class="card">
        <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
            <div style="position: relative; flex: 1; max-width: 360px;">
                <input type="text" id="cariSiswa" class="form-control" placeholder="Cari nama siswa, NIS..." style="font-size: 13px;" onkeyup="filterSiswa()">
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                <button type="button" class="btn btn-outline btn-sm" onclick="setSemuaPresensi('hadir')">Semua Hadir</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="setSemuaPresensi('izin')">Semua Izin</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="setSemuaPresensi('sakit')">Semua Sakit</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="setSemuaPresensi('tidak_hadir')">Semua Tidak Hadir</button>
                <span class="badge badge-neutral">Total: <?= count($daftarSiswa) ?> Siswa Terdaftar</span>
            </div>
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
                            <th style="text-align: center; min-width: 280px;">Kehadiran Hari Ini</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftarSiswa)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                                    Belum ada siswa yang terdaftar di kelas ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $idx = 1; foreach ($daftarSiswa as $s): ?>
                                <?php
                                    $idSiswa = (int)$s['id'];
                                    $statusHariIni = $presensiHariIni[$idSiswa] ?? 'tidak_hadir';
                                    if (!in_array($statusHariIni, ['hadir', 'izin', 'sakit', 'tidak_hadir'], true)) {
                                        $statusHariIni = 'tidak_hadir';
                                    }
                                ?>
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
                                            No. Ortu: <?= e($s['telepon_orang_tua'] ?: '-') ?>
                                        </div>
                                        <div style="font-size: 11px; color: var(--color-text-muted);">
                                            No. Siswa: <?= e($s['nomor_telepon'] ?: '-') ?>
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
                                    <td style="text-align: center;">
                                        <div class="presensi-touchbar" data-id-siswa="<?= $idSiswa ?>">
                                            <input
                                                type="hidden"
                                                class="presensi-value"
                                                name="presensi[<?= $idSiswa ?>]"
                                                value="<?= e($statusHariIni) ?>"
                                                data-id-siswa="<?= $idSiswa ?>"
                                            >
                                            <button type="button" class="presensi-chip chip-hadir <?= $statusHariIni === 'hadir' ? 'is-active' : '' ?>" data-value="hadir" data-id-siswa="<?= $idSiswa ?>">Hadir</button>
                                            <button type="button" class="presensi-chip chip-izin <?= $statusHariIni === 'izin' ? 'is-active' : '' ?>" data-value="izin" data-id-siswa="<?= $idSiswa ?>">Izin</button>
                                            <button type="button" class="presensi-chip chip-sakit <?= $statusHariIni === 'sakit' ? 'is-active' : '' ?>" data-value="sakit" data-id-siswa="<?= $idSiswa ?>">Sakit</button>
                                            <button type="button" class="presensi-chip chip-alfa <?= $statusHariIni === 'tidak_hadir' ? 'is-active' : '' ?>" data-value="tidak_hadir" data-id-siswa="<?= $idSiswa ?>">Tidak Hadir</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (!empty($daftarSiswa)): ?>
        <div class="presensi-submit-bar">
            <div>
                <div style="font-weight: 700; color: var(--color-text-main);">Simpan Kehadiran Hari Ini</div>
                <div style="font-size: 12.5px; color: var(--color-text-muted); margin-top: 2px;">
                    WhatsApp dikirim ke nomor orang tua untuk status yang berubah (Hadir, Izin, Sakit, Tidak Hadir).
                </div>
                <label style="display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 0.55rem; font-size: 12.5px; color: var(--color-text-body);">
                    <input type="checkbox" name="kirim_ulang_wa" value="1" checked>
                    Kirim WhatsApp ke orang tua untuk semua siswa (Hadir / Izin / Sakit / Tidak Hadir)
                </label>
                <label style="display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 0.35rem; font-size: 12.5px; color: var(--color-text-muted);">
                    <input type="checkbox" name="lewati_wa" value="1">
                    Simpan saja tanpa kirim WhatsApp
                </label>
            </div>
            <button type="submit" class="btn btn-primary" id="btnSimpanPresensi">
                Simpan Presensi &amp; Kirim WA
            </button>
        </div>
    <?php endif; ?>
</form>

<script>
function filterSiswa() {
    const filter = document.getElementById('cariSiswa').value.toLowerCase();
    const rows = document.querySelectorAll('#tabelSiswa .baris-siswa');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

const WARNA_PRESENSI = {
    hadir: { bg: '#16A34A', fg: '#FFFFFF' },
    izin: { bg: '#2563EB', fg: '#FFFFFF' },
    sakit: { bg: '#D97706', fg: '#FFFFFF' },
    tidak_hadir: { bg: '#DC2626', fg: '#FFFFFF' }
};

function setPresensi(idSiswa, value) {
    const valueInput = document.querySelector('.presensi-value[data-id-siswa="' + idSiswa + '"]');
    const bar = document.querySelector('.presensi-touchbar[data-id-siswa="' + idSiswa + '"]');
    if (valueInput) valueInput.value = value;
    if (!bar) return;

    bar.querySelectorAll('.presensi-chip').forEach((chip) => {
        const status = chip.getAttribute('data-value');
        const aktif = status === value;
        chip.classList.toggle('is-active', aktif);
        chip.setAttribute('aria-pressed', aktif ? 'true' : 'false');

        if (aktif && WARNA_PRESENSI[status]) {
            chip.style.backgroundColor = WARNA_PRESENSI[status].bg;
            chip.style.color = WARNA_PRESENSI[status].fg;
        } else {
            chip.style.backgroundColor = 'transparent';
            chip.style.color = '#64748B';
        }
    });
}

function setSemuaPresensi(value) {
    document.querySelectorAll('.presensi-value').forEach((input) => {
        setPresensi(input.dataset.idSiswa, value);
    });
}

document.querySelectorAll('.presensi-chip').forEach((chip) => {
    chip.addEventListener('click', function () {
        setPresensi(this.dataset.idSiswa, this.dataset.value);
    });
});

const formPresensi = document.getElementById('formPresensiKelas');
if (formPresensi) {
    formPresensi.addEventListener('submit', function () {
        // Pastikan nilai hidden sinkron dengan chip aktif sebelum POST
        document.querySelectorAll('.presensi-touchbar').forEach((bar) => {
            const aktif = bar.querySelector('.presensi-chip.is-active');
            const hidden = bar.querySelector('.presensi-value');
            if (aktif && hidden) {
                hidden.value = aktif.getAttribute('data-value') || hidden.value;
            }
        });

        const btn = document.getElementById('btnSimpanPresensi');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Menyimpan & mengirim WA...';
        }
    });
}

// Samakan warna awal dengan status tersimpan
document.querySelectorAll('.presensi-value').forEach((input) => {
    setPresensi(input.dataset.idSiswa, input.value);
});
</script>
