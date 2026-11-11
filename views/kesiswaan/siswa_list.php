<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= !empty($kelasTerpilih) ? BASE_URL . '/kesiswaan/kelas?jurusan=' . (int)$kelasTerpilih['id_jurusan'] : BASE_URL . '/kesiswaan/dashboard' ?>" class="badge badge-neutral" style="text-decoration: none;">
                &larr; <?= !empty($kelasTerpilih) ? 'Kembali ke Daftar Kelas' : 'Kembali ke Dashboard' ?>
            </a>
        </div>
        <h1 class="page-title"><?= !empty($kelasTerpilih) ? 'Data Siswa Kelas ' . e($kelasTerpilih['nama_kelas']) : 'Data Induk Siswa' ?></h1>
        <p class="page-subtitle">
            <?= !empty($kelasTerpilih)
                ? 'Daftar siswa khusus kelas ' . e($kelasTerpilih['nama_kelas']) . ' pada jurusan ' . e($kelasTerpilih['kode_jurusan'])
                : 'Master Data Siswa Terdaftar & Status Pembinaan SMK Negeri 9 Kabupaten Tangerang' ?>
        </p>
    </div>
    <button type="button" class="btn btn-primary" onclick="bukaModalTambahSiswa()">+ Tambah Siswa</button>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 220px; max-width: 520px; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; min-width: 220px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--color-text-subtle);">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="cariSiswa" class="form-control" placeholder="Ketik untuk cari nama, NIS, kelas..." style="padding-left: 2.5rem; font-size: 13px;" value="<?= e($filterCari ?? '') ?>" autocomplete="off" oninput="filterTabelSiswaLive()">
            </div>
            <span id="hasilCariSiswa" class="badge badge-neutral" style="font-size: 11px;">Live search</span>
        </div>

        <?php if (empty($kelasTerpilih)): ?>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <select class="form-control" style="font-size: 12.5px; width: auto;" onchange="location.href='<?= BASE_URL ?>/kesiswaan/siswa?jurusan=' + this.value">
                <option value="">Semua Konsentrasi</option>
                <?php foreach ($daftarJurusan as $j): ?>
                    <option value="<?= $j['id'] ?>" <?= ($filterJurusan == $j['id']) ? 'selected' : '' ?>><?= e($j['kode_jurusan']) ?> - <?= e($j['nama_jurusan']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="data-table" id="tabelSiswa">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>NIS / NISN</th>
                        <th>Nama Siswa & Identitas</th>
                        <th>Konsentrasi Keahlian</th>
                        <th>Kelas</th>
                        <th class="col-tahun" style="text-align: center;">Tahun Masuk</th>
                        <th class="col-status" style="text-align: center;">Status</th>
                        <th class="aksi-cell" style="text-align: center; width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarSiswa)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                                Data siswa tidak ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $idx = 1; foreach ($daftarSiswa as $s): ?>
                            <tr class="baris-siswa">
                                <td style="text-align: center; color: var(--color-text-muted); font-weight: 600;"><?= $idx++ ?></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-primary); font-family: monospace; font-size: 13.5px;"><?= e($s['nis']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);">NISN: <?= e($s['nisn'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <?php if (!empty($s['foto_profil']) && $s['foto_profil'] !== 'avatar_default.png' && file_exists(PATH_UPLOADS . '/profil/' . $s['foto_profil'])): ?>
                                            <img src="<?= BASE_URL ?>/uploads/profil/<?= e($s['foto_profil']) ?>" alt="Foto" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                        <?php else: ?>
                                            <div style="width: 34px; height: 34px; border-radius: 50%; background: #EFF6FF; color: var(--color-accent); font-weight: 700; display: flex; align-items: center; justify-content: center; font-size: 12px; border: 1px solid #DBEAFE; flex-shrink: 0;">
                                                <?= strtoupper(substr($s['nama_lengkap'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 700; color: var(--color-text-main);"><?= e($s['nama_lengkap']) ?></div>
                                            <div style="font-size: 11px; color: var(--color-text-muted);">
                                                <?= $s['jenis_kelamin'] === 'L' ? '♂ Laki-laki' : '♀ Perempuan' ?>
                                                <?php if (!empty($s['organisasi']) && $s['organisasi'] === 'OSIS'): ?>
                                                    &bull; <span class="badge badge-warning" style="font-size: 10px; padding: 1px 6px;">OSIS: <?= e($s['jabatan_osis'] ?? 'Pengurus') ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-primary"><?= e($s['kode_jurusan']) ?></span>
                                    <span style="font-size: 12px; margin-left: 4px; color: var(--color-text-body);"><?= e($s['nama_jurusan']) ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-info" style="font-weight: 700; font-size: 12px;"><?= e($s['nama_kelas'] ?? 'Belum Diplot') ?></span>
                                </td>
                                <td class="col-tahun" style="text-align: center; font-weight: 600; color: var(--color-text-body); font-size: 13px;"><?= e($s['tahun_masuk']) ?></td>
                                <td class="col-status" style="text-align: center;">
                                    <?= \App\Helpers\Sanitizer::badgeStatus($s['status_siswa']) ?>
                                </td>
                                <td class="aksi-cell" style="text-align: center;">
                                    <div class="aksi-cell-inner">
                                        <?php if (!empty($s['id_pengguna'])): ?>
                                            <a href="<?= BASE_URL ?>/profil/detail?id_pengguna=<?= (int)$s['id_pengguna'] ?>" class="btn btn-outline btn-sm" title="Ubah Detail Siswa" style="padding: 4px 8px; font-size: 12px;">
                                                Ubah
                                            </a>
                                        <?php endif; ?>
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm btn-hapus-siswa"
                                            style="padding: 4px 8px; font-size: 12px;"
                                            data-id="<?= (int)$s['id'] ?>"
                                            data-nama="<?= e($s['nama_lengkap']) ?>"
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

<div id="modalHapusSiswa" class="modal-overlay" style="display: none;" role="dialog" aria-modal="true" onclick="tutupModalJikaKlikLatar(event, 'modalHapusSiswa')">
    <div class="modal-card">
        <div class="modal-icon-warning" style="background: #FEE2E2; color: #B91C1C;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </div>
        <h3 class="modal-title">Konfirmasi Hapus Siswa</h3>
        <p class="modal-desc">
            Hapus siswa <strong id="namaHapusSiswa"></strong>?
            Data nilai dan absensi terkait ikut terhapus.
        </p>
        <form id="formHapusSiswa" method="POST" action="<?= BASE_URL ?>/kesiswaan/siswa/hapus">
            <?= \App\Helpers\Session::csrfField() ?>
            <input type="hidden" name="id_siswa" id="idHapusSiswa" value="">
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="tutupModalHapusSiswa()">Batal</button>
                <button type="submit" class="btn btn-danger">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

<div id="modalTambahSiswa" class="modal-overlay" style="display: none;" role="dialog" aria-modal="true" onclick="tutupModalJikaKlikLatar(event, 'modalTambahSiswa')">
    <div class="modal-box" style="max-width: 720px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3>Tambah Siswa Baru</h3>
            <button type="button" onclick="tutupModalTambahSiswa()" aria-label="Tutup">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/kesiswaan/siswa/tambah">
            <?= \App\Helpers\Session::csrfField() ?>
            <div class="modal-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">NIS *</label>
                    <input type="text" name="nis" class="form-control" required placeholder="Contoh: 25261001">
                </div>
                <div class="form-group">
                    <label class="form-label">NISN</label>
                    <input type="text" name="nisn" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" class="form-control" required>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tahun Masuk *</label>
                    <input type="number" name="tahun_masuk" class="form-control" min="2018" max="2100" value="<?= (int)date('Y') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Jurusan *</label>
                    <select name="id_jurusan" id="tambahSiswaJurusan" class="form-control" required onchange="filterKelasSiswa()">
                        <option value="">Pilih jurusan</option>
                        <?php foreach ($daftarJurusan as $j): ?>
                            <option value="<?= (int)$j['id'] ?>" <?= (!empty($kelasTerpilih) && (int)$kelasTerpilih['id_jurusan'] === (int)$j['id']) ? 'selected' : '' ?>>
                                <?= e($j['kode_jurusan']) ?> - <?= e($j['nama_jurusan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Kelas (opsional)</label>
                    <select name="id_kelas" id="tambahSiswaKelas" class="form-control">
                        <option value="">Belum diplot</option>
                        <?php foreach ($daftarKelas as $k): ?>
                            <option value="<?= (int)$k['id'] ?>" data-jurusan="<?= (int)$k['id_jurusan'] ?>" <?= (!empty($kelasTerpilih) && (int)$kelasTerpilih['id'] === (int)$k['id']) ? 'selected' : '' ?>>
                                <?= e($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control" value="Tangerang">
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Agama</label>
                    <select name="agama" class="form-control">
                        <?php foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agama): ?>
                            <option value="<?= $agama ?>"><?= $agama ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="nomor_telepon" class="form-control">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Ayah</label>
                    <input type="text" name="nama_ayah" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Ibu</label>
                    <input type="text" name="nama_ibu" class="form-control">
                </div>
                <p style="grid-column: 1 / -1; font-size: 12px; color: var(--color-text-muted); margin: 0;">
                    Akun login otomatis dibuat dengan NIS. Kata sandi awal: <code>smkn9#</code> + 4 digit terakhir NIS.
                </p>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline btn-sm" onclick="tutupModalTambahSiswa()">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Siswa</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterTabelSiswaLive() {
    const input = document.getElementById('cariSiswa');
    const badge = document.getElementById('hasilCariSiswa');
    const filter = (input?.value || '').trim().toLowerCase();
    const rows = document.querySelectorAll('#tabelSiswa .baris-siswa');
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
            : `${rows.length} siswa ditampilkan`;
    }
}

function bukaModalHapusSiswa(idSiswa, namaSiswa) {
    document.getElementById('idHapusSiswa').value = String(idSiswa || '');
    document.getElementById('namaHapusSiswa').textContent = namaSiswa || '';
    document.getElementById('modalHapusSiswa').style.display = 'flex';
}
function tutupModalHapusSiswa() {
    document.getElementById('modalHapusSiswa').style.display = 'none';
}
document.addEventListener('click', function(event) {
    const btn = event.target.closest('.btn-hapus-siswa');
    if (!btn) return;
    event.preventDefault();
    bukaModalHapusSiswa(btn.dataset.id, btn.dataset.nama);
});
function bukaModalTambahSiswa() {
    document.getElementById('modalTambahSiswa').style.display = 'flex';
    filterKelasSiswa();
}
function tutupModalTambahSiswa() {
    document.getElementById('modalTambahSiswa').style.display = 'none';
}
function tutupModalJikaKlikLatar(event, modalId) {
    if (event.target.id === modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
}
function filterKelasSiswa() {
    const jurusanId = document.getElementById('tambahSiswaJurusan').value;
    const kelasSelect = document.getElementById('tambahSiswaKelas');
    Array.from(kelasSelect.options).forEach((opt, idx) => {
        if (idx === 0) {
            opt.hidden = false;
            return;
        }
        const cocok = !jurusanId || opt.dataset.jurusan === jurusanId;
        opt.hidden = !cocok;
        if (!cocok && opt.selected) {
            kelasSelect.value = '';
        }
    });
}
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        tutupModalTambahSiswa();
        tutupModalHapusSiswa();
    }
});
document.addEventListener('DOMContentLoaded', filterTabelSiswaLive);
</script>
