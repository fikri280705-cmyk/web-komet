<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/wali-kelas/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Kelas <?= e($kelas['nama_kelas']) ?></span>
        </div>
        <h1 class="page-title">Jadwal Pelajaran Kelas <?= e($kelas['nama_kelas']) ?></h1>
        <p class="page-subtitle">Jadwal mingguan kegiatan belajar mengajar kelas binaan <?= e($kelas['nama_kelas']) ?> SMKN 9 Kabupaten Tangerang</p>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-primary);">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            Jadwal Belajar Mingguan &bull; <?= e($kelas['nama_kelas']) ?>
        </h3>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <select id="filterHari" class="form-control" style="width: auto; font-size: 13px;" onchange="filterJadwal()">
                <option value="">Semua Hari</option>
                <option value="senin">Senin</option>
                <option value="selasa">Selasa</option>
                <option value="rabu">Rabu</option>
                <option value="kamis">Kamis</option>
                <option value="jumat">Jumat</option>
            </select>
            <span class="badge badge-neutral"><?= count($jadwalKelas) ?> Sesi Belajar</span>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="data-table" id="tabelJadwal">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Hari</th>
                        <th>Waktu Pelaksanaan</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru Pengajar</th>
                        <th>Ruangan Belajar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jadwalKelas)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                                Jadwal pelajaran belum dipetakan untuk kelas ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $idx = 1; foreach ($jadwalKelas as $jdw): ?>
                            <tr class="baris-jadwal" data-hari="<?= strtolower(e($jdw['hari'])) ?>">
                                <td style="text-align: center; color: var(--color-text-muted); font-weight: 600;"><?= $idx++ ?></td>
                                <td>
                                    <div style="font-weight: 800; color: var(--color-primary); font-size: 14px;"><?= e($jdw['hari']) ?></div>
                                </td>
                                <td>
                                    <div style="font-size: 12.5px; font-weight: 600; font-family: monospace; color: var(--color-text-body);">
                                        <?= substr($jdw['jam_mulai'], 0, 5) ?> - <?= substr($jdw['jam_selesai'], 0, 5) ?> WIB
                                    </div>

                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-text-main); font-size: 13.5px;"><?= e($jdw['nama_mapel']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);"><?= e($jdw['kode_mapel']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--color-text-body); font-size: 13px;"><?= e($jdw['nama_guru']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);">NUPTK: <?= e($jdw['nuptk'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($jdw['apakah_lab'])): ?>
                                        <span class="badge badge-warning" style="font-weight: 700;">🔬 <?= e($jdw['ruangan']) ?></span>
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

        <script>
        function filterJadwal() {
            const hariFilter = document.getElementById('filterHari').value.toLowerCase();
            let nomor = 0;
            document.querySelectorAll('#tabelJadwal .baris-jadwal').forEach(row => {
                const hari = row.getAttribute('data-hari') || '';
                const cocok = !hariFilter || hari === hariFilter;
                row.style.display = cocok ? '' : 'none';
                if (cocok) {
                    row.cells[0].textContent = ++nomor;
                }
            });
        }
        </script>
    </div>
</div>
