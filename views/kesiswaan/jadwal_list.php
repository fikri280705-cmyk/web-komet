<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/kesiswaan/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Kalender Akademik</span>
        </div>
        <h1 class="page-title"><?= !empty($kelasJadwal) ? 'Jadwal KBM Kelas ' . e($kelasJadwal['nama_kelas']) : 'Jadwal KBM Semua Jurusan' ?></h1>
        <p class="page-subtitle">
            <?= !empty($kelasJadwal)
                ? 'Jadwal Kegiatan Belajar Mengajar kelas ' . e($kelasJadwal['nama_kelas'])
                : 'Jadwal Kegiatan Belajar Mengajar Terpadu SMKN 9 Kabupaten Tangerang' ?>
        </p>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; flex: 1;">
            <div style="position: relative; min-width: 200px;">
                <input type="text" id="cariJadwal" class="form-control" placeholder="Cari mapel, guru, ruang..." style="font-size: 13px;" onkeyup="filterJadwal()">
            </div>
            <?php if (empty($kelasJadwal)): ?>
                <select id="pilihJurusan" class="form-control" style="width: auto; font-size: 13px;" onchange="filterJadwal()">
                    <option value="">Semua Konsentrasi Keahlian</option>
                    <?php foreach ($daftarJurusan as $j): ?>
                        <option value="<?= strtolower(e($j['kode_jurusan'])) ?>"><?= e($j['kode_jurusan']) ?> - <?= e($j['nama_jurusan']) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <select id="pilihHari" class="form-control" style="width: auto; font-size: 13px;" onchange="filterJadwal()">
                <option value="">Semua Hari</option>
                <option value="senin">Senin</option>
                <option value="selasa">Selasa</option>
                <option value="rabu">Rabu</option>
                <option value="kamis">Kamis</option>
                <option value="jumat">Jumat</option>
            </select>
        </div>
        <div>
            <span class="badge badge-neutral">Total: <?= count($daftarJadwal) ?> Jadwal KBM</span>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="data-table" id="tabelJadwal">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Hari & Jam</th>
                        <th>Kelas & Konsentrasi</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru Pengampu</th>
                        <th>Ruangan / Fasilitas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarJadwal)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                                Belum ada data jadwal KBM yang terdaftar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $idx = 1; foreach ($daftarJadwal as $jdw): ?>
                            <tr class="baris-jadwal" data-jurusan="<?= strtolower(e($jdw['kode_jurusan'] ?? '')) ?>" data-hari="<?= strtolower(e($jdw['hari'])) ?>">
                                <td style="text-align: center; color: var(--color-text-muted); font-weight: 600;"><?= $idx++ ?></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-primary); font-size: 13.5px;"><?= e($jdw['hari']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted); font-family: monospace;">
                                        <?= substr($jdw['jam_mulai'], 0, 5) ?> - <?= substr($jdw['jam_selesai'], 0, 5) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info" style="font-weight: 700;"><?= e($jdw['nama_kelas']) ?></span>
                                    <span class="badge badge-primary" style="margin-left: 4px;"><?= e($jdw['kode_jurusan'] ?? '') ?></span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-text-main); font-size: 13.5px;"><?= e($jdw['nama_mapel']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);"><?= e($jdw['kode_mapel']) ?> &bull; <?= e($jdw['kelompok_mapel'] ?? 'Produktif') ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--color-text-body); font-size: 13px;"><?= e($jdw['nama_guru']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted); font-family: monospace;">NUPTK: <?= e($jdw['nuptk'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($jdw['apakah_lab'])): ?>
                                        <span class="badge badge-warning" style="font-weight: 700;">🔬 <?= e($jdw['ruangan'] ?? 'Laboratorium') ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">🏫 <?= e($jdw['ruangan'] ?? 'Ruang Teori') ?></span>
                                    <?php endif; ?>
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
function filterJadwal() {
    const textSearch = document.getElementById('cariJadwal').value.toLowerCase();
    const jurusanSelect = document.getElementById('pilihJurusan');
    const jurusanFilter = jurusanSelect ? jurusanSelect.value.toLowerCase() : '';
    const hariFilter = document.getElementById('pilihHari').value.toLowerCase();
    const rows = document.querySelectorAll('#tabelJadwal .baris-jadwal');
    let nomor = 0;

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const jur = row.getAttribute('data-jurusan') || '';
        const hari = row.getAttribute('data-hari') || '';

        const matchText = text.includes(textSearch);
        const matchJur = !jurusanFilter || jur.includes(jurusanFilter);
        const matchHari = !hariFilter || hari === hariFilter;

        const cocok = matchText && matchJur && matchHari;
        row.style.display = cocok ? '' : 'none';
        if (cocok) {
            row.cells[0].textContent = ++nomor;
        }
    });
}
</script>
