<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/siswa/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-success">
                <span class="badge-dot"></span>
                Telah Disahkan (Final)
            </span>
        </div>
        <h1 class="page-title">Transkrip Nilai & e-Rapor Siswa</h1>
        <p class="page-subtitle">Daftar Nilai Akademik & Capaian Kompetensi Kejuruan SMKN 9 Kabupaten Tangerang</p>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
        <select class="form-control" aria-label="Pilih tahun ajaran" style="font-size: 13px; width: auto;" onchange="location.href='<?= BASE_URL ?>/siswa/nilai?tahun=' + this.value">
            <?php foreach ($daftarTahunAjaran as $tahun): ?>
                <option value="<?= (int)$tahun['id'] ?>" <?= ($tahunTerpilih && (int)$tahunTerpilih['id'] === (int)$tahun['id']) ? 'selected' : '' ?>>
                    <?= e($tahun['nama_tahun']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select class="form-control" aria-label="Pilih semester" style="font-size: 13px; width: auto;" onchange="location.href='<?= BASE_URL ?>/siswa/nilai?tahun=<?= (int)($tahunTerpilih['id'] ?? 0) ?>&semester=' + this.value" <?= empty($daftarSemester) ? 'disabled' : '' ?>>
            <?php foreach ($daftarSemester as $sem): ?>
                <option value="<?= (int)$sem['id'] ?>" <?= ($idSemester == $sem['id']) ? 'selected' : '' ?>>
                    Semester <?= ucfirst(e($sem['jenis_semester'])) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button onclick="window.print()" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.5rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <span>Cetak Transkrip</span>
        </button>
    </div>
</div>

<!-- Kartu Profil Transkrip Siswa -->
<div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(to right, #FFFFFF, #F8FAFC);">
    <div class="card-body" style="padding: 1.35rem 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.04em;">Nama Lengkap</div>
                <div style="font-size: 14.5px; font-weight: 800; color: var(--color-text-main); margin-top: 2px;"><?= e($siswa['nama_lengkap']) ?></div>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.04em;">Nomor Induk Siswa (NIS)</div>
                <div style="font-size: 14.5px; font-weight: 700; color: var(--color-primary); margin-top: 2px; font-family: monospace;"><?= e($siswa['nis']) ?></div>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.04em;">Kelas & Konsentrasi</div>
                <div style="font-size: 14.5px; font-weight: 700; color: var(--color-text-main); margin-top: 2px;"><?= e($kelasTahunTerpilih['nama_kelas'] ?? '-') ?> (<?= e($kelasTahunTerpilih['kode_jurusan'] ?? '-') ?>)</div>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.04em;">Periode Rapor</div>
                <div style="font-size: 14.5px; font-weight: 700; color: var(--color-accent); margin-top: 2px;">
                    <?php if ($tahunTerpilih && $semesterTerpilih): ?>
                        <?= e($tahunTerpilih['nama_tahun']) ?> &bull; Semester <?= ucfirst(e($semesterTerpilih['jenis_semester'])) ?>
                    <?php else: ?>
                        Belum ada riwayat periode akademik
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kartu Rekap Kehadiran Siswa -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-primary);">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            Rekap Kehadiran Siswa
        </h3>
        <span class="badge badge-neutral">Presensi Semester</span>
    </div>
    <div class="card-body">
        <div class="grid-3" style="margin-bottom: 1rem;">
            <div style="background: #EFF6FF; border: 1px solid #DBEAFE; border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                <div style="font-size: 11px; font-weight: 700; color: var(--color-primary); text-transform: uppercase;">Sakit</div>
                <div style="font-size: 26px; font-weight: 800; color: var(--color-primary); margin-top: 4px;">
                    <?= $kehadiran['sakit'] !== null ? (int)$kehadiran['sakit'] . ' ' : 'Belum direkap' ?>
                    <?php if ($kehadiran['sakit'] !== null): ?><span style="font-size: 13px; font-weight: 600;">Hari</span><?php endif; ?>
                </div>
            </div>
            <div style="background: #FEF3C7; border: 1px solid #FDE68A; border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                <div style="font-size: 11px; font-weight: 700; color: #D97706; text-transform: uppercase;">Izin</div>
                <div style="font-size: 26px; font-weight: 800; color: #D97706; margin-top: 4px;">
                    <?= $kehadiran['izin'] !== null ? (int)$kehadiran['izin'] . ' ' : 'Belum direkap' ?>
                    <?php if ($kehadiran['izin'] !== null): ?><span style="font-size: 13px; font-weight: 600;">Hari</span><?php endif; ?>
                </div>
            </div>
            <div style="background: #FEE2E2; border: 1px solid #FECACA; border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                <div style="font-size: 11px; font-weight: 700; color: #DC2626; text-transform: uppercase;">Tanpa Keterangan (Alfa)</div>
                <div style="font-size: 26px; font-weight: 800; color: #DC2626; margin-top: 4px;">
                    <?= $kehadiran['alfa'] !== null ? (int)$kehadiran['alfa'] . ' ' : 'Belum direkap' ?>
                    <?php if ($kehadiran['alfa'] !== null): ?><span style="font-size: 13px; font-weight: 600;">Hari</span><?php endif; ?>
                </div>
            </div>
        </div>
        <?php if (!empty($kehadiran['catatan'])): ?>
            <div style="background: var(--color-bg-subtle, #f8fafc); padding: 0.75rem 1rem; border-radius: var(--radius-md); border: 1px solid var(--color-border); font-size: 13px;">
                <strong>Catatan Wali Kelas:</strong> <?= e($kehadiran['catatan']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-accent);">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            Tabel Capaian Nilai Rapor
        </h3>
        <span class="badge badge-neutral">Total: <?= count($nilaiFinal) ?> Mata Pelajaran</span>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($nilaiFinal)): ?>
            <div style="padding: 3.5rem 1.5rem; text-align: center; color: var(--color-text-muted);">
                <div style="width: 64px; height: 64px; margin: 0 auto 1rem; border-radius: 50%; background: #F1F5F9; display: flex; align-items: center; justify-content: center; color: var(--color-text-muted);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                </div>
                <div style="font-size: 16px; font-weight: 700; color: var(--color-text-main);">Belum Ada Nilai Rapor Final</div>
                <p style="font-size: 13px; margin-top: 0.35rem; max-width: 400px; margin-left: auto; margin-right: auto;">Nilai Anda pada semester ini belum disahkan atau masih dalam proses perekapan.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="data-table" id="tabelRapor">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th style="cursor: pointer; user-select: none;" onclick="sortRapor(1)">Mata Pelajaran ⇅</th>
                            <th>Guru Pengampu</th>
                            <th style="text-align: right; cursor: pointer; user-select: none;" onclick="sortRapor(3)">Tugas (20%) ⇅</th>
                            <th style="text-align: right; cursor: pointer; user-select: none;" onclick="sortRapor(4)">UTS (20%) ⇅</th>
                            <th style="text-align: right; cursor: pointer; user-select: none;" onclick="sortRapor(5)">UAS (20%) ⇅</th>
                            <th style="text-align: right; cursor: pointer; user-select: none;" onclick="sortRapor(6)">Praktik (40%) ⇅</th>
                            <th style="text-align: right; cursor: pointer; user-select: none;" onclick="sortRapor(7)">Nilai Akhir ⇅</th>
                            <th style="text-align: center; cursor: pointer; user-select: none;" onclick="sortRapor(8)">Predikat ⇅</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nilaiFinal as $idx => $n): ?>
                            <tr class="baris-nilai">
                                <td style="text-align: center; color: var(--color-text-muted); font-weight: 600;"><?= $idx + 1 ?></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-primary); font-size: 14px;"><?= e($n['nama_mapel']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted); font-family: monospace;"><?= e($n['kode_mapel']) ?></div>
                                </td>
                                <td style="font-size: 13px; color: var(--color-text-body);"><?= e($n['nama_guru_pengajar']) ?></td>
                                <td style="text-align: right; font-weight: 600;"><?= number_format($n['nilai_tugas'], 1) ?></td>
                                <td style="text-align: right; font-weight: 600;"><?= number_format($n['nilai_uts'], 1) ?></td>
                                <td style="text-align: right; font-weight: 600;"><?= number_format($n['nilai_uas'], 1) ?></td>
                                <td style="text-align: right; font-weight: 700; color: var(--color-secondary);"><?= number_format($n['nilai_praktik'], 1) ?></td>
                                <td style="text-align: right;">
                                    <span style="font-weight: 800; font-size: 15px; color: var(--color-primary);"><?= number_format($n['nilai_akhir'], 1) ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge <?= in_array($n['predikat_huruf'], ['A', 'B']) ? 'badge-success' : 'badge-warning' ?>" style="font-weight: 800; font-size: 12px; padding: 3px 12px;">
                                        <?= e($n['predikat_huruf']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
let raporAsc = true;
function sortRapor(colIdx) {
    const table = document.getElementById('tabelRapor');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr.baris-nilai'));
    raporAsc = !raporAsc;

    rows.sort((a, b) => {
        let valA = a.children[colIdx].textContent.trim();
        let valB = b.children[colIdx].textContent.trim();
        let numA = parseFloat(valA);
        let numB = parseFloat(valB);

        if (!isNaN(numA) && !isNaN(numB)) {
            return raporAsc ? numA - numB : numB - numA;
        }
        return raporAsc ? valA.localeCompare(valB) : valB.localeCompare(valA);
    });

    rows.forEach((row, i) => {
        row.children[0].textContent = i + 1;
        tbody.appendChild(row);
    });
}
</script>
