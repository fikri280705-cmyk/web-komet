<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/kesiswaan/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Tenaga Pendidik</span>
        </div>
        <h1 class="page-title">Data Guru & Pegawai</h1>
        <p class="page-subtitle">Daftar Tenaga Pendidik, Kepala Program Keahlian, dan Penugasan Wali Kelas SMKN 9 Kabupaten Tangerang</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="bukaModalTambahGuru()">+ Tambah Guru</button>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div style="position: relative; flex: 1; max-width: 400px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--color-text-subtle);">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="cariGuru" class="form-control" placeholder="Ketik untuk cari nama, NUPTK, mapel..." style="padding-left: 2.5rem; font-size: 13px;" autocomplete="off" oninput="filterTabelGuruLive()">
        </div>
        <div>
            <span id="hasilCariGuru" class="badge badge-neutral">Total: <?= count($daftarGuru) ?> Tenaga Pendidik</span>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="data-table" id="tabelGuru">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>NUPTK / NIP</th>
                        <th>Nama Guru & Kualifikasi</th>
                        <th>Bidang / Konsentrasi</th>
                        <th>Mata Pelajaran</th>
                        <th>Penugasan / Jabatan</th>
                        <th class="aksi-cell" style="text-align: center; width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarGuru)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                                Data guru tidak ditemukan.
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
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <?php if (!empty($g['foto_profil']) && $g['foto_profil'] !== 'avatar_default.png' && file_exists(PATH_UPLOADS . '/profil/' . $g['foto_profil'])): ?>
                                            <img src="<?= BASE_URL ?>/uploads/profil/<?= e($g['foto_profil']) ?>" alt="Foto" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                        <?php else: ?>
                                            <div style="width: 36px; height: 36px; border-radius: 50%; background: #EFF6FF; color: var(--color-secondary); font-weight: 700; display: flex; align-items: center; justify-content: center; font-size: 12px; border: 1px solid #DBEAFE; flex-shrink: 0;">
                                                <?= strtoupper(substr($g['nama_lengkap'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 700; color: var(--color-text-main);"><?= e($g['nama_lengkap']) ?></div>
                                            <div style="font-size: 11px; color: var(--color-text-muted);">
                                                <?= $g['jenis_kelamin'] === 'L' ? '♂ Laki-laki' : '♀ Perempuan' ?> &bull; <?= e($g['pendidikan_terakhir'] ?? 'S1') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($g['nama_jurusan'])): ?>
                                        <span class="badge badge-primary"><?= e($g['kode_jurusan']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">Umum</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-size: 13px; font-weight: 600; color: var(--color-text-body);"><?= e($g['mapel_utama'] ?? 'Mata Pelajaran Umum') ?></span>
                                </td>
                                <td>
                                    <?php if ($g['kode_peran'] === 'kesiswaan'): ?>
                                        <span class="badge badge-primary" style="font-weight: 700;">Bidang Kesiswaan</span>
                                    <?php elseif ($g['kode_peran'] === 'kaprog'): ?>
                                        <span class="badge badge-warning" style="font-weight: 700;">Kepala Program (<?= e($g['kode_jurusan'] ?? '') ?>)</span>
                                    <?php elseif (!empty($g['nama_kelas_wali'])): ?>
                                        <span class="badge badge-success" style="font-weight: 700;">Wali Kelas <?= e($g['nama_kelas_wali']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">Guru Pengajar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="aksi-cell" style="text-align: center;">
                                    <div class="aksi-cell-inner">
                                        <?php if (!empty($g['id_pengguna'])): ?>
                                            <a href="<?= BASE_URL ?>/profil/detail?id_pengguna=<?= (int)$g['id_pengguna'] ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 12px;">
                                                Ubah
                                            </a>
                                        <?php endif; ?>
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm btn-hapus-guru"
                                            style="padding: 4px 8px; font-size: 12px;"
                                            data-id="<?= (int)$g['id'] ?>"
                                            data-nama="<?= e($g['nama_lengkap']) ?>"
                                        >Hapus</button>
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

<div id="modalHapusGuru" class="modal-overlay" style="display: none;" role="dialog" aria-modal="true" onclick="tutupModalJikaKlikLatar(event, 'modalHapusGuru')">
    <div class="modal-card">
        <div class="modal-icon-warning" style="background: #FEE2E2; color: #B91C1C;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </div>
        <h3 class="modal-title">Konfirmasi Hapus Guru</h3>
        <p class="modal-desc">
            Hapus guru <strong id="namaHapusGuru"></strong>?
            Jika masih punya penugasan mengajar, akun akan dinonaktifkan.
        </p>
        <form id="formHapusGuru" method="POST" action="<?= BASE_URL ?>/kesiswaan/guru/hapus">
            <?= \App\Helpers\Session::csrfField() ?>
            <input type="hidden" name="id_guru" id="idHapusGuru" value="">
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="tutupModalHapusGuru()">Batal</button>
                <button type="submit" class="btn btn-danger">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

<div id="modalTambahGuru" class="modal-overlay" style="display: none;" role="dialog" aria-modal="true" onclick="tutupModalJikaKlikLatar(event, 'modalTambahGuru')">
    <div class="modal-box" style="max-width: 720px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3>Tambah Guru Baru</h3>
            <button type="button" onclick="tutupModalTambahGuru()" aria-label="Tutup">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/kesiswaan/guru/tambah">
            <?= \App\Helpers\Session::csrfField() ?>
            <div class="modal-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">NUPTK *</label>
                    <input type="text" name="nuptk" class="form-control" required placeholder="Nomor NUPTK">
                </div>
                <div class="form-group">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" class="form-control" required>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Jabatan / Peran *</label>
                    <select name="jabatan_tugas" class="form-control" required>
                        <option value="Guru Pengajar">Guru Pengajar</option>
                        <option value="Wali Kelas">Wali Kelas</option>
                        <option value="Kepala Program Keahlian">Kepala Program Keahlian</option>
                        <option value="Kesiswaan & Kurikulum">Kesiswaan & Kurikulum</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Homebase Jurusan</label>
                    <select name="id_jurusan" class="form-control">
                        <option value="">Umum / Lintas Jurusan</option>
                        <?php foreach ($daftarJurusan as $j): ?>
                            <option value="<?= (int)$j['id'] ?>"><?= e($j['kode_jurusan']) ?> - <?= e($j['nama_jurusan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mapel Utama</label>
                    <input type="text" name="mapel_utama" class="form-control" placeholder="Contoh: Pemrograman Web">
                </div>
                <div class="form-group">
                    <label class="form-label">Penugasan Wali Kelas</label>
                    <select name="id_kelas_wali" class="form-control">
                        <option value="">Tidak ditugaskan</option>
                        <?php foreach ($daftarKelas as $k): ?>
                            <option value="<?= (int)$k['id'] ?>"><?= e($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="opsional">
                </div>
                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="nomor_telepon" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Pendidikan Terakhir</label>
                    <input type="text" name="pendidikan_terakhir" class="form-control" value="S1 Kependidikan">
                </div>
                <div class="form-group">
                    <label class="form-label">Agama</label>
                    <select name="agama" class="form-control">
                        <?php foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agama): ?>
                            <option value="<?= $agama ?>"><?= $agama ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2"></textarea>
                </div>
                <p style="grid-column: 1 / -1; font-size: 12px; color: var(--color-text-muted); margin: 0;">
                    Akun login otomatis dibuat dengan NUPTK. Kata sandi awal: <code>gurusmkn9#</code> + 6 digit terakhir NUPTK.
                </p>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline btn-sm" onclick="tutupModalTambahGuru()">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Guru</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterTabelGuruLive() {
    const input = document.getElementById('cariGuru');
    const badge = document.getElementById('hasilCariGuru');
    const filter = (input?.value || '').trim().toLowerCase();
    const rows = document.querySelectorAll('#tabelGuru .baris-guru');
    let terlihat = 0;

    rows.forEach((row) => {
        const text = row.textContent.toLowerCase();
        const cocok = !filter || text.includes(filter);
        row.style.display = cocok ? '' : 'none';
        if (cocok) {
            terlihat += 1;
            const noCell = row.querySelector('td:first-child');
            if (noCell) noCell.textContent = String(terlihat);
        }
    });

    if (badge) {
        badge.textContent = filter
            ? `${terlihat} hasil untuk "${input.value.trim()}"`
            : `Total: ${rows.length} Tenaga Pendidik`;
    }
}
function bukaModalHapusGuru(idGuru, namaGuru) {
    document.getElementById('idHapusGuru').value = String(idGuru || '');
    document.getElementById('namaHapusGuru').textContent = namaGuru || '';
    document.getElementById('modalHapusGuru').style.display = 'flex';
}
function tutupModalHapusGuru() {
    document.getElementById('modalHapusGuru').style.display = 'none';
}
document.addEventListener('click', function(event) {
    const btn = event.target.closest('.btn-hapus-guru');
    if (!btn) return;
    event.preventDefault();
    bukaModalHapusGuru(btn.dataset.id, btn.dataset.nama);
});
function bukaModalTambahGuru() {
    document.getElementById('modalTambahGuru').style.display = 'flex';
}
function tutupModalTambahGuru() {
    document.getElementById('modalTambahGuru').style.display = 'none';
}
function tutupModalJikaKlikLatar(event, modalId) {
    if (event.target.id === modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
}
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        tutupModalTambahGuru();
        tutupModalHapusGuru();
    }
});
document.addEventListener('DOMContentLoaded', filterTabelGuruLive);
</script>
