<div class="page-header">
    <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
        <span class="badge badge-primary">Keamanan Akun</span>
        <span class="badge badge-success">
            <span class="badge-dot"></span>
            Sesi Terverifikasi
        </span>
    </div>
    <h1 class="page-title">Pengaturan Akun & Keamanan</h1>
    <p class="page-subtitle">Kelola informasi kontak akun pribadi, foto profil, dan perbarui kata sandi untuk mengamankan hak akses Anda</p>
</div>

<div class="grid-2">
    <!-- Profil Pengguna -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-primary);">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Informasi Profil Akun
            </h3>
            <span class="badge badge-info"><?= e($user['nama_peran']) ?></span>
        </div>
        <div class="card-body">
            <div style="display: flex; align-items: center; gap: 1.25rem; margin-bottom: 1.75rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); flex-wrap: wrap;">
                <?php if (!empty($user['foto_profil']) && $user['foto_profil'] !== 'avatar_default.png' && file_exists(PATH_UPLOADS . '/profil/' . $user['foto_profil'])): ?>
                    <img src="<?= BASE_URL ?>/uploads/profil/<?= e($user['foto_profil']) ?>" alt="Foto Profil" style="width: 72px; height: 72px; border-radius: var(--radius-lg); object-fit: cover; border: 2px solid var(--color-primary);">
                <?php else: ?>
                    <div class="user-avatar-mini" style="width: 72px; height: 72px; font-size: 28px; border-radius: var(--radius-lg);">
                        <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div style="flex: 1; min-width: 200px;">
                    <h4 style="font-size: 1.2rem; font-weight: 800; color: var(--color-text-main); line-height: 1.2;"><?= e($user['nama_lengkap']) ?></h4>
                    <p style="font-size: 13.5px; color: var(--color-text-muted); margin-top: 4px; font-weight: 600;">
                        <?= in_array($user['kode_peran'], ['siswa', 'osis']) ? 'NIS: ' . e($user['nomor_induk']) : 'NUPTK: ' . e($user['nomor_induk']) ?>
                    </p>
                    <div style="margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap;">
                        <span class="badge badge-primary" style="font-size: 11px;">
                            <?= e($user['kode_peran']) ?> &bull; <?= e($user['nama_peran']) ?>
                        </span>
                        <?php if (!empty($detailAkun['nama_jurusan'])): ?>
                            <span class="badge badge-neutral" style="font-size: 11px;"><?= e($detailAkun['nama_jurusan']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.25rem; padding: 1rem; background: var(--color-bg-subtle, #f8fafc); border-radius: var(--radius-md); border: 1px dashed var(--color-border);">
                <form action="<?= BASE_URL ?>/profil/foto/upload" method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <?= \App\Helpers\Session::csrfField() ?>
                    <div style="flex: 1; min-width: 180px;">
                        <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Ubah Foto Profil (Maks. 2MB, JPG/PNG)</label>
                        <input type="file" name="foto_profil" accept="image/jpeg,image/png,image/webp" class="form-control" style="font-size: 12px; padding: 4px 8px;" required>
                    </div>
                    <button type="submit" class="btn btn-outline" style="align-self: flex-end; padding: 7px 14px; font-size: 12px;">
                        Unggah Foto
                    </button>
                </form>
            </div>

            <div class="table-container" style="margin-bottom: 1.5rem;">
                <table class="data-table">
                    <tbody>
                        <tr>
                            <td style="font-weight: 700; width: 150px; color: var(--color-text-muted);">
                                <?= in_array($user['kode_peran'], ['siswa', 'osis']) ? 'Nomor Induk Siswa (NIS)' : 'NUPTK' ?>
                            </td>
                            <td style="font-weight: 700; color: var(--color-text-main); font-family: monospace; font-size: 14px;">
                                <?= e($user['nomor_induk']) ?>
                            </td>
                        </tr>
                        <?php if (in_array($user['kode_peran'], ['siswa', 'osis']) && !empty($detailAkun['nisn'])): ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--color-text-muted);">NISN</td>
                                <td style="font-family: monospace; font-weight: 600;"><?= e($detailAkun['nisn']) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($detailAkun['tanggal_lahir'])): ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--color-text-muted);">Tanggal Lahir</td>
                                <td>
                                    <?= !empty($detailAkun['tempat_lahir']) ? e($detailAkun['tempat_lahir']) . ', ' : '' ?>
                                    <?= date('d F Y', strtotime($detailAkun['tanggal_lahir'])) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($detailAkun['nama_kelas'])): ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--color-text-muted);">Kelas</td>
                                <td><span class="badge badge-success"><?= e($detailAkun['nama_kelas']) ?></span></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--color-text-muted);">Email Terdaftar</td>
                            <td style="font-weight: 600; color: var(--color-text-main);"><?= e($user['email']) ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700; color: var(--color-text-muted);">Hak Akses (Role)</td>
                            <td>
                                <span class="badge badge-neutral" style="font-weight: 700;"><?= e($user['kode_peran']) ?></span>
                                <span style="font-size: 12.5px; margin-left: 6px;"><?= e($user['nama_peran']) ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div>
                <a href="<?= BASE_URL ?>/profil/detail" class="btn btn-primary btn-block" style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span>Ubah Detail Data Profil</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Form Ganti Kata Sandi -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-accent);">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Perbarui Kata Sandi
            </h3>
            <span class="badge badge-warning">
                <span class="badge-dot"></span>
                Autentikasi
            </span>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>/pengaturan-akun/password" method="POST">
                <?= \App\Helpers\Session::csrfField() ?>

                <div class="form-group">
                    <label class="form-label" for="password_lama">Kata Sandi Saat Ini</label>
                    <input type="password" id="password_lama" name="password_lama" class="form-control" required placeholder="Masukkan kata sandi lama">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_baru">Kata Sandi Baru</label>
                    <input type="password" id="password_baru" name="password_baru" class="form-control" required minlength="8" placeholder="Minimal 8 karakter unik">
                    <small style="color: var(--color-text-muted); display: block; margin-top: 0.35rem;">
                        Gunakan kombinasi huruf besar, huruf kecil, dan angka untuk keamanan maksimal.
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_konfirmasi">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" id="password_konfirmasi" name="password_konfirmasi" class="form-control" required minlength="8" placeholder="Ketik ulang kata sandi baru">
                </div>

                <div style="margin-top: 1.75rem;">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Simpan Perubahan Kata Sandi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
