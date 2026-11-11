<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/kaprog/<?= !empty($kelasJadwal) ? 'kelas' : 'dashboard' ?>" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke <?= !empty($kelasJadwal) ? 'Daftar Kelas' : 'Dashboard' ?></a>
            <span class="badge badge-primary">Jadwal Kejuruan</span>
        </div>
        <h1 class="page-title"><?= !empty($kelasJadwal) ? 'Jadwal KBM Kelas ' . e($kelasJadwal['nama_kelas']) : 'Jadwal KBM Jurusan ' . e($jurusan['nama_jurusan']) ?></h1>
        <p class="page-subtitle">
            <?= !empty($kelasJadwal)
                ? 'Agenda pembelajaran khusus kelas ' . e($kelasJadwal['nama_kelas'])
                : 'Agenda perkuliahan dan praktik kejuruan rumpun ' . e($jurusan['kode_jurusan']) . ' SMKN 9 Kabupaten Tangerang' ?>
        </p>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 0.75rem; flex: 1; align-items: center; flex-wrap: wrap;">
            <input type="text" id="cariJadwal" class="form-control" placeholder="Cari mapel, guru, kelas..." style="max-width: 300px; font-size: 13px;" onkeyup="filterJadwal()">
            <?php if (empty($kelasJadwal)): ?>
                <select id="filterKelas" class="form-control" style="width: auto; font-size: 13px;" onchange="filterJadwal()">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($daftarKelas as $k): ?>
                        <option value="<?= strtolower(e($k['nama_kelas'])) ?>"><?= e($k['nama_kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <select id="filterHari" class="form-control" style="width: auto; font-size: 13px;" onchange="filterJadwal()">
                <option value="">Semua Hari</option>
                <option value="senin">Senin</option>
                <option value="selasa">Selasa</option>
                <option value="rabu">Rabu</option>
                <option value="kamis">Kamis</option>
                <option value="jumat">Jumat</option>
            </select>
        </div>
        <span class="badge badge-neutral">Total: <?= count($jadwalJurusan) ?> Sesi KBM</span>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="data-table" id="tabelJadwal">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Hari & Jam</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru Pengampu</th>
                        <th>Ruangan / Fasilitas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jadwalJurusan)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                                <?= !empty($kelasJadwal)
                                    ? 'Belum ada jadwal KBM untuk kelas ini.'
                                    : 'Belum ada jadwal KBM untuk jurusan ini.' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $idx = 1; foreach ($jadwalJurusan as $jdw): ?>
                            <tr class="baris-jadwal" data-kelas="<?= strtolower(e($jdw['nama_kelas'])) ?>" data-hari="<?= strtolower(e($jdw['hari'])) ?>">
                                <td style="text-align: center; color: var(--color-text-muted); font-weight: 600;"><?= $idx++ ?></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-primary); font-size: 13.5px;"><?= e($jdw['hari']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted); font-family: monospace;">
                                        <?= \App\Helpers\Sanitizer::formatJam($jdw['jam_mulai']) ?> - <?= \App\Helpers\Sanitizer::formatJam($jdw['jam_selesai']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-primary" style="font-weight: 700;"><?= e($jdw['nama_kelas']) ?></span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-text-main); font-size: 13.5px;"><?= e($jdw['nama_mapel']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);"><?= e($jdw['kode_mapel'] ?? '') ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--color-text-body); font-size: 13px;"><?= e($jdw['nama_guru']) ?></div>
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
    const kelasSelect = document.getElementById('filterKelas');
    const kelasFilter = kelasSelect ? kelasSelect.value.toLowerCase() : '';
    const hariFilter = document.getElementById('filterHari').value.toLowerCase();
    const rows = document.querySelectorAll('#tabelJadwal .baris-jadwal');
    let nomor = 0;

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const kls = row.getAttribute('data-kelas') || '';
        const hari = row.getAttribute('data-hari') || '';

        const matchText = text.includes(textSearch);
        const matchKls = !kelasFilter || kls === kelasFilter;
        const matchHari = !hariFilter || hari === hariFilter;

        const cocok = matchText && matchKls && matchHari;
        row.style.display = cocok ? '' : 'none';
        if (cocok) {
            row.cells[0].textContent = ++nomor;
        }
    });
}
</script>
