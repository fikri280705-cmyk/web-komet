<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/kaprog/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-warning">Fasilitas Praktik</span>
        </div>
        <h1 class="page-title">Jadwal Penggunaan Lab & Bengkel</h1>
        <p class="page-subtitle">Alokasi jadwal praktikum laboratorium komputer dan bengkel kejuruan untuk rumpun <?= e($jurusan['nama_jurusan']) ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-warning);">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
            Jadwal Fasilitas Praktik &bull; <?= e($jurusan['nama_jurusan']) ?>
        </h3>
        <?php if (!empty($jadwalLab)): ?>
            <?php
            $daftarRuangan = array_values(array_filter(array_unique(array_column($jadwalLab, 'ruangan'))));
            $daftarKelasLab = array_values(array_unique(array_column($jadwalLab, 'nama_kelas')));
            sort($daftarRuangan, SORT_NATURAL | SORT_FLAG_CASE);
            sort($daftarKelasLab, SORT_NATURAL | SORT_FLAG_CASE);
            ?>
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <select id="filterHari" class="form-control" style="width: auto; font-size: 13px;" onchange="filterJadwalLab()">
                    <option value="">Semua Hari</option>
                    <option value="senin">Senin</option>
                    <option value="selasa">Selasa</option>
                    <option value="rabu">Rabu</option>
                    <option value="kamis">Kamis</option>
                    <option value="jumat">Jumat</option>
                </select>
                <select id="filterRuangan" class="form-control" style="width: auto; font-size: 13px;" onchange="filterJadwalLab()">
                    <option value="">Semua Ruangan</option>
                    <?php foreach ($daftarRuangan as $ruangan): ?>
                        <option value="<?= e(strtolower($ruangan)) ?>"><?= e($ruangan) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="filterKelas" class="form-control" style="width: auto; font-size: 13px;" onchange="filterJadwalLab()">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($daftarKelasLab as $namaKelas): ?>
                        <option value="<?= e(strtolower($namaKelas)) ?>"><?= e($namaKelas) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="badge badge-neutral" id="jumlahJadwalLab"><?= count($jadwalLab) ?> Sesi Praktikum</span>
            </div>
        <?php else: ?>
            <span class="badge badge-neutral">0 Sesi Praktikum</span>
        <?php endif; ?>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($jadwalLab)): ?>
            <div style="padding: 3.5rem 2rem; text-align: center; color: var(--color-text-muted);">
                <div style="width: 56px; height: 56px; margin: 0 auto 1.25rem; border-radius: 50%; background: #F8FAFC; border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: center; color: var(--color-text-muted);">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                    </svg>
                </div>
                <h4 style="font-weight: 800; color: var(--color-text-main); font-size: 15px; margin-bottom: 0.5rem;">
                    Tidak ada penggunaan lab/bengkel untuk konsentrasi keahlian <?= e($jurusan['nama_jurusan']) ?>
                </h4>
                <p style="font-size: 13px; max-width: 500px; margin: 0 auto; line-height: 1.5;">
                    Konsentrasi keahlian ini menyelenggarakan seluruh aktivitas belajar mengajar di ruang teori dan simulasi perkantoran, tanpa penggunaan laboratorium komputer/bengkel teknik khusus.
                </p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th>Hari & Sesi Waktu</th>
                            <th>Fasilitas Lab / Bengkel</th>
                            <th>Kelas Praktikan</th>
                            <th>Mata Pelajaran Praktikum</th>
                            <th>Instruktur / Guru Pengampu</th>
                            <th style="text-align: center;">Status Ruangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $idx = 1; foreach ($jadwalLab as $lab): ?>
                            <tr class="baris-jadwal-lab"
                                data-hari="<?= e(strtolower($lab['hari'])) ?>"
                                data-ruangan="<?= e(strtolower($lab['ruangan'] ?? '')) ?>"
                                data-kelas="<?= e(strtolower($lab['nama_kelas'])) ?>">
                                <td style="text-align: center; color: var(--color-text-muted); font-weight: 600;"><?= $idx++ ?></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-primary); font-size: 13.5px;"><?= e($lab['hari']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted); font-family: monospace;">
                                        <?= substr($lab['jam_mulai'], 0, 5) ?> - <?= substr($lab['jam_selesai'], 0, 5) ?> WIB
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-warning" style="font-weight: 700; font-size: 12.5px;">
                                        🔬 <?= e($lab['ruangan'] ?? 'Laboratorium') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-primary" style="font-weight: 700;"><?= e($lab['nama_kelas']) ?></span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-text-main); font-size: 13.5px;"><?= e($lab['nama_mapel']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);"><?= e($lab['kode_mapel']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--color-text-body); font-size: 13px;"><?= e($lab['nama_guru']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);">NUPTK: <?= e($lab['nuptk'] ?? '-') ?></div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-success">
                                        <span class="badge-dot"></span> Terjadwal
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="tidakAdaJadwalLab" style="display: none;">
                            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
                                Tidak ada jadwal yang cocok dengan filter yang dipilih.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($jadwalLab)): ?>
                <script>
                function filterJadwalLab() {
                    const hariFilter = document.getElementById('filterHari').value;
                    const ruanganFilter = document.getElementById('filterRuangan').value;
                    const kelasFilter = document.getElementById('filterKelas').value;
                    const rows = document.querySelectorAll('.baris-jadwal-lab');
                    let nomor = 0;

                    rows.forEach(row => {
                        const cocok =
                            (!hariFilter || row.dataset.hari === hariFilter) &&
                            (!ruanganFilter || row.dataset.ruangan === ruanganFilter) &&
                            (!kelasFilter || row.dataset.kelas === kelasFilter);
                        row.style.display = cocok ? '' : 'none';
                        if (cocok) {
                            row.cells[0].textContent = ++nomor;
                        }
                    });

                    document.getElementById('tidakAdaJadwalLab').style.display = nomor ? 'none' : '';
                    document.getElementById('jumlahJadwalLab').textContent = nomor + ' Sesi Praktikum';
                }
                </script>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
