<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/osis/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Formulir Kegiatan</span>
        </div>
        <h1 class="page-title">Buat Usulan Event Sekolah Baru</h1>
        <p class="page-subtitle">Isi rincian kegiatan dan unggah poster resmi untuk diajukan ke Pembina Kesiswaan</p>
    </div>
</div>

<div class="card" style="max-width: 820px; margin: 0 auto;">
    <div class="card-header">
        <h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-primary);">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Formulir Rincian Acara & Poster
        </h3>
        <span class="badge badge-neutral">Tahap 1: Pengajuan</span>
    </div>

    <div class="card-body">
        <form action="<?= BASE_URL ?>/osis/event/simpan" method="POST" enctype="multipart/form-data">
            <?= \App\Helpers\Session::csrfField() ?>

            <div class="form-group">
                <label class="form-label" for="judul">
                    Judul Acara / Kegiatan <span style="color: var(--color-danger);">*</span>
                </label>
                <input type="text" id="judul" name="judul" class="form-control" placeholder="Contoh: Turnamen Futsal & E-Sport Class Meeting SMKN 9" required>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label" for="id_kategori">
                        Kategori Kegiatan <span style="color: var(--color-danger);">*</span>
                    </label>
                    <select id="id_kategori" name="id_kategori" class="form-control" required>
                        <option value="3">Organisasi & OSIS</option>
                        <option value="2">Lomba & Kejuaraan</option>
                        <option value="1">Akademik & Ujian</option>
                        <option value="4">Pentas Seni & Budaya</option>
                        <option value="5">Pengumuman Resmi</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="penyelenggara">
                        Penyelenggara Kegiatan <span style="color: var(--color-danger);">*</span>
                    </label>
                    <input type="text" id="penyelenggara" name="penyelenggara" class="form-control" value="Pengurus OSIS SMKN 9 Tangerang" required>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label" for="waktu_mulai">
                        Tanggal & Jam Mulai <span style="color: var(--color-danger);">*</span>
                    </label>
                    <input type="datetime-local" id="waktu_mulai" name="waktu_mulai" class="form-control" required value="<?= date('Y-m-d\T08:00') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="waktu_selesai">
                        Tanggal & Jam Selesai <span style="color: var(--color-danger);">*</span>
                    </label>
                    <input type="datetime-local" id="waktu_selesai" name="waktu_selesai" class="form-control" required value="<?= date('Y-m-d\T15:00', strtotime('+3 days')) ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="lokasi">
                    Lokasi Spesifik Kegiatan <span style="color: var(--color-danger);">*</span>
                </label>
                <input type="text" id="lokasi" name="lokasi" class="form-control" placeholder="Contoh: Lapangan Serbaguna & Aula Graha SMKN 9" value="SMKN 9 Tangerang" required>
            </div>

            <!-- Area Unggah Foto Poster dengan Live Preview -->
            <div class="form-group">
                <label class="form-label" for="input_foto_poster">Unggah Foto Poster Resmi (JPG/PNG/WebP, Max 2MB)</label>
                <input type="file" id="input_foto_poster" name="foto_poster" class="form-control" accept="image/jpeg,image/png,image/webp">
                <small style="color: var(--color-text-muted); display: block; margin-top: 0.35rem;">
                    Format berkas yang didukung: JPG, PNG, atau WebP. Foto akan tampil di mading digital sekolah setelah disetujui.
                </small>

                <!-- Kontainer Live Preview Gambar -->
                <div id="container_preview_poster" style="display: none; margin-top: 1rem; text-align: center; border: 1.5px dashed var(--color-border); border-radius: var(--radius-md); padding: 1.25rem; background: #F8FAFC;">
                    <div style="font-size: 11.5px; font-weight: 700; color: var(--color-primary); margin-bottom: 0.65rem; text-transform: uppercase; letter-spacing: 0.04em;">
                        Pratinjau Foto Poster:
                    </div>
                    <img id="preview_foto_poster" src="" alt="Pratinjau Poster" style="max-width: 100%; max-height: 260px; object-fit: contain; border-radius: var(--radius-sm); box-shadow: var(--shadow-sm); border: 1px solid var(--color-border);">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="deskripsi">
                    Deskripsi & Rundown Kegiatan <span style="color: var(--color-danger);">*</span>
                </label>
                <textarea id="deskripsi" name="deskripsi" class="form-control" rows="5" placeholder="Jelaskan tujuan acara, target peserta, susunan acara/rundown, dan nomor narahubung panitia..." required></textarea>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; border-top: 1px solid var(--color-border); padding-top: 1.5rem; flex-wrap: wrap;">
                <button type="submit" name="aksi_submit" value="draf" class="btn btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Sebagai Draf</span>
                </button>
                <button type="submit" name="aksi_submit" value="ajukan" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                    <span>Ajukan ke Kesiswaan (Submit)</span>
                </button>
            </div>
        </form>
    </div>
</div>
