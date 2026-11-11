<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/kesiswaan/jurusan" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Jurusan</a>
            <span class="badge badge-primary">Kelas Binaan</span>
        </div>
        <h1 class="page-title">Kelas Jurusan <?= e($jurusan['nama_jurusan']) ?></h1>
        <p class="page-subtitle">Daftar kelas pada konsentrasi keahlian <?= e($jurusan['kode_jurusan']) ?></p>
    </div>
</div>

<?php if (empty($daftarKelas)): ?>
    <div class="card">
        <div class="card-body" style="padding: 3rem 1.5rem; text-align: center; color: var(--color-text-muted);">
            Belum ada kelas yang terdaftar pada jurusan ini.
        </div>
    </div>
<?php else: ?>
    <div class="grid-2" style="margin-bottom: 2rem;">
        <?php foreach ($daftarKelas as $kelas): ?>
            <div class="card">
                <div class="card-header">
                    <div>
                        <span class="badge badge-primary" style="font-size: 13px; font-weight: 800;"><?= e($kelas['nama_kelas']) ?></span>
                    </div>
                    <span class="badge badge-neutral">Tingkat <?= e($kelas['tingkat']) ?></span>
                </div>
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div>
                            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--color-text-muted);">Jumlah Siswa Aktif:</div>
                            <div style="font-size: 24px; font-weight: 800; color: var(--color-text-main);">
                                <?= (int)$kelas['jumlah_siswa_aktif'] ?>
                                <span style="font-size: 14px; font-weight: 500; color: var(--color-text-muted);">Siswa</span>
                            </div>
                        </div>
                        <div class="stat-icon primary" style="width: 48px; height: 48px;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                            </svg>
                        </div>
                    </div>

                    <div style="background: var(--color-bg-subtle, #f8fafc); padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--color-border); font-size: 12.5px;">
                        <div style="font-weight: 700; color: var(--color-text-muted); font-size: 11px; text-transform: uppercase;">Ruang Belajar Utama:</div>
                        <div style="font-weight: 600; color: var(--color-primary); margin-top: 2px;">🏫 Ruang Teori Kelas <?= e($kelas['nama_kelas']) ?></div>
                    </div>
                </div>
                <div style="padding: 0.85rem 1.25rem; border-top: 1px solid var(--color-border); background: var(--color-bg-subtle, #f8fafc); display: flex; justify-content: flex-end; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="<?= BASE_URL ?>/kesiswaan/siswa?jurusan=<?= (int)$jurusan['id'] ?>&kelas=<?= (int)$kelas['id'] ?>" class="btn btn-outline btn-sm">Lihat Siswa</a>
                    <a href="<?= BASE_URL ?>/kesiswaan/jadwal?kelas=<?= (int)$kelas['id'] ?>" class="btn btn-primary btn-sm">Lihat Jadwal</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
