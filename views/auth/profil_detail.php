<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <span class="badge badge-primary">Profil Data Pribadi</span>
                <?php if ($isKesiswaan && !$isSelf): ?>
                    <span class="badge badge-warning">Mode Administrator Kesiswaan</span>
                <?php endif; ?>
            </div>
            <h1 class="page-title">Ubah Detail Data Profil</h1>
            <p class="page-subtitle">Perbarui data diri, kontak, dan informasi kependudukan secara valid dan terverifikasi</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/pengaturan-akun" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Pengaturan</span>
            </a>
        </div>
    </div>
</div>

<?php
$isStudent = in_array($pengguna['kode_peran'], ['siswa', 'osis']);
?>

<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-primary);">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Formulir Pembaruan Data &bull; <?= e($pengguna['nama_lengkap']) ?>
        </h3>
        <span class="badge badge-info"><?= e($pengguna['nama_peran']) ?></span>
    </div>

    <div class="card-body">
        <?php if (!$isKesiswaan): ?>
            <div class="alert alert-info" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <span>Data identitas pokok (NIS/NUPTK, Nama Lengkap, Peran, Kelas/Jurusan) dikunci demi validitas data akademik. Hubungi Bagian Kesiswaan jika terdapat ketidaksesuaian.</span>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/profil/detail/simpan" method="POST">
            <?= \App\Helpers\Session::csrfField() ?>
            <input type="hidden" name="id_pengguna" value="<?= (int)$pengguna['id'] ?>">

            <h4 style="font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-primary); margin-bottom: 1rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
                1. Data Identitas Pokok
            </h4>

            <div class="grid-2" style="margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label" for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="<?= e($detailAkun['nama_lengkap'] ?? $pengguna['nama_lengkap']) ?>" <?= $isKesiswaan ? 'required' : 'readonly style="background-color: var(--color-bg-subtle, #f1f5f9); cursor: not-allowed;"' ?>>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nomor_induk"><?= $isStudent ? 'Nomor Induk Siswa (NIS)' : 'NUPTK' ?></label>
                    <input type="text" id="nomor_induk" name="<?= $isStudent ? 'nis' : 'nuptk' ?>" class="form-control" value="<?= e($pengguna['nomor_induk']) ?>" <?= $isKesiswaan ? 'required' : 'readonly style="background-color: var(--color-bg-subtle, #f1f5f9); cursor: not-allowed; font-family: monospace;"' ?>>
                </div>

                <?php if ($isStudent): ?>
                    <div class="form-group">
                        <label class="form-label" for="nisn">NISN</label>
                        <input type="text" id="nisn" name="nisn" class="form-control" value="<?= e($detailAkun['nisn'] ?? '') ?>" <?= $isKesiswaan ? '' : 'readonly style="background-color: var(--color-bg-subtle, #f1f5f9); cursor: not-allowed; font-family: monospace;"' ?>>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kelas Saat Ini</label>
                        <input type="text" class="form-control" value="<?= e($detailAkun['nama_kelas'] ?? '-') ?>" readonly style="background-color: var(--color-bg-subtle, #f1f5f9); cursor: not-allowed;">
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label class="form-label" for="nip">NIP</label>
                        <input type="text" id="nip" name="nip" class="form-control" value="<?= e($detailAkun['nip'] ?? '') ?>" <?= $isKesiswaan ? '' : 'readonly style="background-color: var(--color-bg-subtle, #f1f5f9); cursor: not-allowed; font-family: monospace;"' ?>>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="mapel_utama">Mata Pelajaran Diampu</label>
                        <input type="text" id="mapel_utama" name="mapel_utama" class="form-control" value="<?= e($detailAkun['mapel_utama'] ?? '') ?>">
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="jenis_kelamin">Jenis Kelamin</label>
                    <?php if ($isKesiswaan): ?>
                        <select id="jenis_kelamin" name="jenis_kelamin" class="form-control">
                            <option value="L" <?= ($detailAkun['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= ($detailAkun['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    <?php else: ?>
                        <input type="text" class="form-control" value="<?= ($detailAkun['jenis_kelamin'] ?? '') === 'L' ? 'Laki-laki' : 'Perempuan' ?>" readonly style="background-color: var(--color-bg-subtle, #f1f5f9); cursor: not-allowed;">
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="agama">Agama</label>
                    <select id="agama" name="agama" class="form-control">
                        <?php foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agm): ?>
                            <option value="<?= $agm ?>" <?= ($detailAkun['agama'] ?? '') === $agm ? 'selected' : '' ?>><?= $agm ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <h4 style="font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-primary); margin-bottom: 1rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
                2. Kelahiran & Kontak Personal
            </h4>

            <div class="grid-2" style="margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label" for="tempat_lahir">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" class="form-control" value="<?= e($detailAkun['tempat_lahir'] ?? '') ?>" placeholder="Kota / Kabupaten Kelahiran">
                </div>

                <div class="form-group">
                    <label class="form-label" for="tanggal_lahir">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" value="<?= e($detailAkun['tanggal_lahir'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="nomor_telepon">Nomor Telepon / WhatsApp</label>
                    <input type="text" id="nomor_telepon" name="nomor_telepon" class="form-control" value="<?= e($detailAkun['nomor_telepon'] ?? '') ?>" placeholder="08xxxxxxxxxx">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= e($pengguna['email'] ?? ($detailAkun['email'] ?? '')) ?>" placeholder="nama@email.com">
                </div>
            </div>

            <h4 style="font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-primary); margin-bottom: 1rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
                3. Alamat Domisili
            </h4>

            <div class="grid-2" style="margin-bottom: 1.5rem;">
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label" for="alamat">Alamat Lengkap</label>
                    <textarea id="alamat" name="alamat" class="form-control" rows="2" placeholder="Jalan, RT/RW, Dusun/Kelurahan, Kecamatan"><?= e($detailAkun['alamat'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="kabupaten">Kabupaten / Kota</label>
                    <input type="text" id="kabupaten" name="kabupaten" class="form-control" value="<?= e($detailAkun['kabupaten'] ?? 'Kabupaten Tangerang') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="provinsi">Provinsi</label>
                    <input type="text" id="provinsi" name="provinsi" class="form-control" value="<?= e($detailAkun['provinsi'] ?? 'Banten') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="kode_pos">Kode Pos</label>
                    <input type="text" id="kode_pos" name="kode_pos" class="form-control" value="<?= e($detailAkun['kode_pos'] ?? '15710') ?>">
                </div>
            </div>

            <?php if ($isStudent): ?>
                <h4 style="font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-primary); margin-bottom: 1rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
                    4. Data Orang Tua / Wali
                </h4>

                <div class="grid-2" style="margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="nama_ayah">Nama Ayah Kandung</label>
                        <input type="text" id="nama_ayah" name="nama_ayah" class="form-control" value="<?= e($detailAkun['nama_ayah'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nama_ibu">Nama Ibu Kandung</label>
                        <input type="text" id="nama_ibu" name="nama_ibu" class="form-control" value="<?= e($detailAkun['nama_ibu'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="pekerjaan_ortu">Pekerjaan Orang Tua</label>
                        <input type="text" id="pekerjaan_ortu" name="pekerjaan_ortu" class="form-control" value="<?= e($detailAkun['pekerjaan_ortu'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="telepon_orang_tua">No. Telepon Orang Tua</label>
                        <input type="text" id="telepon_orang_tua" name="telepon_orang_tua" class="form-control" value="<?= e($detailAkun['telepon_orang_tua'] ?? '') ?>">
                    </div>
                </div>
            <?php else: ?>
                <h4 style="font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-primary); margin-bottom: 1rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
                    4. Kepegawaian & Kualifikasi
                </h4>

                <div class="grid-2" style="margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="pendidikan_terakhir">Pendidikan Terakhir</label>
                        <input type="text" id="pendidikan_terakhir" name="pendidikan_terakhir" class="form-control" value="<?= e($detailAkun['pendidikan_terakhir'] ?? 'S1 Pendidikan') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="npwp">NPWP</label>
                        <input type="text" id="npwp" name="npwp" class="form-control" value="<?= e($detailAkun['npwp'] ?? '') ?>">
                    </div>
                </div>
            <?php endif; ?>

            <div style="margin-top: 2rem; display: flex; gap: 1rem; align-items: center;">
                <button type="submit" class="btn btn-primary btn-lg" style="display: inline-flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Perubahan Data</span>
                </button>
                <a href="<?= BASE_URL ?>/pengaturan-akun" class="btn btn-outline btn-lg">Batal</a>
            </div>
        </form>
    </div>
</div>

