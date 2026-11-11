<div class="page-header">
    <h1 class="page-title">Tahun Ajaran & Kenaikan Kelas</h1>
    <p class="page-subtitle">Siapkan kurikulum, wali kelas, penugasan guru, dan jadwal sebelum periode baru diaktifkan.</p>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header"><h3>Buat Draft Tahun Ajaran</h3></div>
    <div class="card-body">
        <p style="margin-top: 0; color: var(--color-text-muted);">Draft tidak mengubah periode aktif. Tahun ajaran harus berurutan dan Semester Ganjil/Genap harus berada di dalam rentang tahunnya.</p>
        <form method="post" action="<?= BASE_URL ?>/kesiswaan/tahun-ajaran/buat-draft">
            <?= \App\Helpers\Session::csrfField() ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <label>Tanggal mulai tahun
                    <input class="form-control" type="date" name="tanggal_mulai" required>
                </label>
                <label>Tanggal selesai tahun
                    <input class="form-control" type="date" name="tanggal_selesai" required>
                </label>
                <label>Ganjil mulai
                    <input class="form-control" type="date" name="ganjil_mulai" required>
                </label>
                <label>Ganjil selesai
                    <input class="form-control" type="date" name="ganjil_selesai" required>
                </label>
                <label>Genap mulai
                    <input class="form-control" type="date" name="genap_mulai" required>
                </label>
                <label>Genap selesai
                    <input class="form-control" type="date" name="genap_selesai" required>
                </label>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Buat Draft</button>
        </form>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header"><h3>Periode Akademik</h3></div>
    <div class="card-body">
        <?php if ($periodeAktif): ?>
            <p style="margin-top: 0;"><strong>Aktif sekarang:</strong> <?= e($periodeAktif['nama_tahun']) ?> &bull; Semester <?= ucfirst(e($periodeAktif['jenis_semester'])) ?></p>
        <?php else: ?>
            <div class="alert alert-error">Tidak ada pasangan tahun ajaran dan semester aktif. Periksa data periode sebelum melanjutkan.</div>
        <?php endif; ?>
        <form method="get" action="<?= BASE_URL ?>/kesiswaan/tahun-ajaran" style="display: flex; flex-wrap: wrap; gap: .75rem; align-items: end;">
            <label>Tahun ajaran
                <select class="form-control" name="tahun">
                    <?php foreach ($daftarTahun as $tahun): ?>
                        <option value="<?= (int)$tahun['id'] ?>" <?= ($tahunTerpilih && (int)$tahunTerpilih['id'] === (int)$tahun['id']) ? 'selected' : '' ?>>
                            <?= e($tahun['nama_tahun']) ?><?= (int)$tahun['apakah_aktif'] === 1 ? ' (Aktif)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Semester
                <select class="form-control" name="semester" <?= empty($daftarSemester) ? 'disabled' : '' ?>>
                    <?php foreach ($daftarSemester as $semester): ?>
                        <option value="<?= (int)$semester['id'] ?>" <?= ($semesterTerpilih && (int)$semesterTerpilih['id'] === (int)$semester['id']) ? 'selected' : '' ?>>
                            Semester <?= ucfirst(e($semester['jenis_semester'])) ?><?= (int)$semester['apakah_aktif'] === 1 ? ' (Aktif)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Kelas
                <select class="form-control" name="kelas" <?= empty($daftarKelas) ? 'disabled' : '' ?>>
                    <?php foreach ($daftarKelas as $kelas): ?>
                        <option value="<?= (int)$kelas['id'] ?>" <?= ($kelasTerpilih && (int)$kelasTerpilih['id'] === (int)$kelas['id']) ? 'selected' : '' ?>>
                            <?= e($kelas['nama_kelas']) ?> - <?= e($kelas['nama_jurusan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="btn btn-outline">Tampilkan</button>
        </form>
        <?php if ($tahunTerpilih && $semesterTerpilih): ?>
            <div style="margin-top: 1rem; padding: .85rem 1rem; border-radius: var(--radius-md); background: <?= $semesterDapatDisiapkan ? '#ecfdf5' : '#f8fafc' ?>; border: 1px solid <?= $semesterDapatDisiapkan ? '#a7f3d0' : 'var(--color-border)' ?>;">
                <strong><?= $semesterDapatDisiapkan ? 'Mode persiapan terbuka' : 'Mode baca saja' ?></strong>
                <span style="color: var(--color-text-muted);">
                    <?= $semesterDapatDisiapkan
                        ? 'Perubahan hanya diizinkan untuk semester Genap yang akan datang atau tahun ajaran draft.'
                        : 'Semester ini sudah berjalan atau merupakan periode lampau, sehingga tidak dapat diubah dari halaman persiapan.' ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header"><h3>Impor Persiapan dari XLSX / CSV</h3></div>
    <div class="card-body">
        <p style="margin-top: 0; color: var(--color-text-muted);">
            Unggah wali kelas, kurikulum, penugasan pengajar, dan jadwal untuk tahun ajaran yang dipilih.
            Semester pada setiap baris ditentukan dari kolom <code>semester</code>.
        </p>
        <p>
            <a class="btn btn-outline" href="<?= BASE_URL ?>/kesiswaan/tahun-ajaran/impor/template">Unduh Template XLSX</a>
        </p>
        <ul style="padding-left: 1.25rem; color: var(--color-text-muted);">
            <li>Gunakan kode jurusan, kelas, mata pelajaran, dan NUPTK yang sudah terdaftar.</li>
            <li>Wali kelas hanya dapat diimpor ke tahun ajaran mendatang yang belum aktif.</li>
            <li>Data wali, kurikulum, dan pengajar yang sudah ada akan diperbarui; jadwal yang bentrok membatalkan seluruh impor.</li>
            <li>Jika ada kesalahan pada baris mana pun, semua perubahan dibatalkan. Ukuran file maksimal 5 MB dan maksimal 5.000 baris.</li>
        </ul>
        <?php if ($tahunTerpilih): ?>
            <form method="post" action="<?= BASE_URL ?>/kesiswaan/tahun-ajaran/impor" enctype="multipart/form-data" style="display: flex; flex-wrap: wrap; gap: .75rem; align-items: end;">
                <?= \App\Helpers\Session::csrfField() ?>
                <input type="hidden" name="id_tahun" value="<?= (int)$tahunTerpilih['id'] ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                <label>Berkas XLSX atau CSV
                    <input class="form-control" type="file" name="file_impor" accept=".xlsx,.csv" required>
                </label>
                <button type="submit" class="btn btn-primary">Validasi dan Impor</button>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">Buat atau pilih tahun ajaran terlebih dahulu sebelum mengimpor data.</div>
        <?php endif; ?>
    </div>
</div>

<?php if ($kesiapanSemester): ?>
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3>Kesiapan Semester <?= ucfirst(e($kesiapanSemester['semester']['jenis_semester'] ?? '')) ?></h3>
            <span class="badge <?= $kesiapanSemester['siap'] ? 'badge-success' : 'badge-warning' ?>">
                <?= $kesiapanSemester['siap'] ? 'Siap' : 'Belum siap' ?>
            </span>
        </div>
        <div class="card-body">
            <?php if ($kesiapanSemester['siap']): ?>
                <p style="margin: 0;">Semua kelas memiliki kurikulum, wali kelas, pengajar, jadwal sesuai jumlah pertemuan, dan tidak ada bentrok.</p>
            <?php else: ?>
                <ul style="margin: 0; padding-left: 1.25rem;">
                    <?php foreach (array_slice($kesiapanSemester['errors'], 0, 20) as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php if (count($kesiapanSemester['errors']) > 20): ?>
                    <p>Dan <?= count($kesiapanSemester['errors']) - 20 ?> masalah lainnya.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($daftarWali && $tahunTerpilih && $semesterTerpilih): ?>
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header"><h3>Wali Kelas Tahun <?= e($tahunTerpilih['nama_tahun']) ?></h3></div>
        <div class="card-body" style="overflow-x: auto;">
            <p style="margin-top: 0; color: var(--color-text-muted);">Satu guru hanya dapat menjadi wali untuk satu kelas pada tahun ajaran yang sama.</p>
            <table class="table">
                <thead><tr><th>Kelas</th><th>Wali saat ini</th><th>Tetapkan / ganti wali</th></tr></thead>
                <tbody>
                    <?php foreach ($daftarWali as $wali): ?>
                        <tr>
                            <td><?= e($wali['nama_kelas']) ?></td>
                            <td><?= e($wali['nama_guru'] ?? 'Belum ditetapkan') ?></td>
                            <td>
                                <?php if ($waliDapatDisiapkan): ?>
                                <form method="post" action="<?= BASE_URL ?>/kesiswaan/tahun-ajaran/wali/simpan" style="display: flex; gap: .5rem; min-width: 280px;">
                                    <?= \App\Helpers\Session::csrfField() ?>
                                    <input type="hidden" name="id_tahun" value="<?= (int)$tahunTerpilih['id'] ?>">
                                    <input type="hidden" name="id_semester" value="<?= (int)$semesterTerpilih['id'] ?>">
                                    <input type="hidden" name="id_kelas" value="<?= (int)$wali['id_kelas'] ?>">
                                    <select class="form-control" name="id_guru" required>
                                        <option value="">Pilih guru</option>
                                        <?php foreach ($daftarGuru as $guru): ?>
                                            <option value="<?= (int)$guru['id'] ?>" <?= ((int)($wali['id_guru'] ?? 0) === (int)$guru['id']) ? 'selected' : '' ?>>
                                                <?= e($guru['nama_lengkap']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-outline" type="submit">Simpan</button>
                                </form>
                                <?php else: ?>
                                    <?= e($wali['nama_guru'] ?? 'Belum ditetapkan') ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if ($semesterTerpilih && $tahunTerpilih && $kelasTerpilih): ?>
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3>Kurikulum & Jadwal: <?= e($kelasTerpilih['nama_kelas']) ?></h3>
            <span class="badge badge-neutral"><?= e($kelasTerpilih['nama_jurusan']) ?> &bull; Semester <?= ucfirst(e($semesterTerpilih['jenis_semester'])) ?></span>
        </div>
        <div class="card-body">
            <?php if ($semesterDapatDisiapkan): ?>
                <form method="post" action="<?= BASE_URL ?>/kesiswaan/tahun-ajaran/template/simpan" style="display: flex; flex-wrap: wrap; gap: .75rem; align-items: end; margin-bottom: 1rem;">
                    <?= \App\Helpers\Session::csrfField() ?>
                    <input type="hidden" name="id_tahun" value="<?= (int)$tahunTerpilih['id'] ?>">
                    <input type="hidden" name="id_semester" value="<?= (int)$semesterTerpilih['id'] ?>">
                    <input type="hidden" name="id_kelas" value="<?= (int)$kelasTerpilih['id'] ?>">
                    <input type="hidden" name="id_jurusan" value="<?= (int)$kelasTerpilih['id_jurusan'] ?>">
                    <input type="hidden" name="tingkat" value="<?= (int)$kelasTerpilih['tingkat'] ?>">
                    <label>Mata pelajaran
                        <select class="form-control" name="id_mapel" required>
                            <option value="">Pilih mapel</option>
                            <?php foreach ($daftarMapel as $mapel): ?>
                                <option value="<?= (int)$mapel['id'] ?>"><?= e($mapel['nama_mapel']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Pertemuan / minggu
                        <input class="form-control" type="number" name="pertemuan_per_minggu" min="1" max="25" value="2" required>
                    </label>
                    <button class="btn btn-primary" type="submit">Tambah / perbarui template</button>
                </form>
            <?php endif; ?>

            <?php if (!$templateKurikulum): ?>
                <p class="text-muted">Template kurikulum untuk jurusan dan tingkat kelas ini belum tersedia.</p>
            <?php else: ?>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($setupKelas as $setup): ?>
                        <?php
                        $jumlahPenugasan = (int)$setup['jumlah_penugasan'];
                        $idPenugasan = (int)($setup['id_penugasan'] ?? 0);
                        $jadwalMapel = $jadwalPerMapel[(int)$setup['id_mapel']] ?? [];
                        ?>
                        <div style="border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                                <strong><?= e($setup['nama_mapel']) ?></strong>
                                <span class="badge badge-neutral"><?= (int)$setup['pertemuan_per_minggu'] ?> pertemuan/minggu</span>
                            </div>
                            <?php if ($semesterDapatDisiapkan): ?>
                                <div style="display: flex; gap: .75rem; flex-wrap: wrap; align-items: end; margin-top: .75rem;">
                                    <form method="post" action="<?= BASE_URL ?>/kesiswaan/tahun-ajaran/penugasan/simpan" style="display: flex; gap: .5rem; align-items: end; flex-wrap: wrap;">
                                        <?= \App\Helpers\Session::csrfField() ?>
                                        <input type="hidden" name="id_tahun" value="<?= (int)$tahunTerpilih['id'] ?>">
                                        <input type="hidden" name="id_semester" value="<?= (int)$semesterTerpilih['id'] ?>">
                                        <input type="hidden" name="id_kelas" value="<?= (int)$kelasTerpilih['id'] ?>">
                                        <input type="hidden" name="id_mapel" value="<?= (int)$setup['id_mapel'] ?>">
                                        <label>Guru pengajar
                                            <select class="form-control" name="id_guru" required>
                                                <option value="">Pilih guru</option>
                                                <?php foreach ($daftarGuru as $guru): ?>
                                                    <option value="<?= (int)$guru['id'] ?>" <?= ((int)($setup['id_guru'] ?? 0) === (int)$guru['id']) ? 'selected' : '' ?>>
                                                        <?= e($guru['nama_lengkap']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <button class="btn btn-outline" type="submit">Simpan pengajar</button>
                                    </form>
                                    <?php if ($jumlahPenugasan > 0): ?>
                                        <form method="post" action="<?= BASE_URL ?>/kesiswaan/tahun-ajaran/penugasan/hapus" onsubmit="return confirm('Hapus semua penugasan dan jadwal mapel ini? Data nilai yang sudah ada akan melindungi penugasan dari penghapusan.')">
                                            <?= \App\Helpers\Session::csrfField() ?>
                                            <input type="hidden" name="id_tahun" value="<?= (int)$tahunTerpilih['id'] ?>">
                                            <input type="hidden" name="id_semester" value="<?= (int)$semesterTerpilih['id'] ?>">
                                            <input type="hidden" name="id_kelas" value="<?= (int)$kelasTerpilih['id'] ?>">
                                            <input type="hidden" name="id_mapel" value="<?= (int)$setup['id_mapel'] ?>">
                                            <button class="btn btn-outline" type="submit">Hapus penugasan</button>
                                        </form>
                                    <?php endif; ?>
                                    <span style="color: var(--color-text-muted);">Jadwal: <?= count($jadwalMapel) ?> / <?= (int)$setup['pertemuan_per_minggu'] ?></span>
                                </div>
                            <?php else: ?>
                                <p style="margin: .5rem 0 0;">Pengajar: <?= e($setup['nama_guru'] ?? 'Belum ditetapkan') ?> &bull; Jadwal: <?= count($jadwalMapel) ?></p>
                            <?php endif; ?>

                            <?php if ($jumlahPenugasan > 1): ?>
                                <p class="alert alert-error" style="margin: .75rem 0 0;">Terdapat <?= $jumlahPenugasan ?> penugasan mapel ini; rapikan duplikasi sebelum melanjutkan.</p>
                            <?php elseif ($semesterDapatDisiapkan && $idPenugasan > 0): ?>
                                <form method="post" action="<?= BASE_URL ?>/kesiswaan/tahun-ajaran/jadwal/simpan" style="display: flex; flex-wrap: wrap; gap: .6rem; align-items: end; margin-top: .75rem;">
                                    <?= \App\Helpers\Session::csrfField() ?>
                                    <input type="hidden" name="id_tahun" value="<?= (int)$tahunTerpilih['id'] ?>">
                                    <input type="hidden" name="id_semester" value="<?= (int)$semesterTerpilih['id'] ?>">
                                    <input type="hidden" name="id_kelas" value="<?= (int)$kelasTerpilih['id'] ?>">
                                    <input type="hidden" name="id_penugasan" value="<?= $idPenugasan ?>">
                                    <label>Hari
                                        <select class="form-control" name="hari" required>
                                            <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari): ?>
                                                <option value="<?= $hari ?>"><?= $hari ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <label>Mulai <input class="form-control" type="time" name="jam_mulai" required></label>
                                    <label>Selesai <input class="form-control" type="time" name="jam_selesai" required></label>
                                    <label>Ruangan <input class="form-control" type="text" name="ruangan" maxlength="50" value="Ruang Teori 101" required></label>
                                    <label style="display: flex; align-items: center; gap: .4rem;"><input type="checkbox" name="apakah_lab" value="1"> Lab/Bengkel</label>
                                    <button class="btn btn-outline" type="submit">Tambah jadwal</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($jadwalMapel): ?>
                                <div style="overflow-x: auto; margin-top: .75rem;">
                                    <table class="table">
                                        <thead><tr><th>Hari</th><th>Jam</th><th>Ruangan</th><th>Jenis</th><?php if ($semesterDapatDisiapkan): ?><th>Aksi</th><?php endif; ?></tr></thead>
                                        <tbody>
                                            <?php foreach ($jadwalMapel as $jadwal): ?>
                                                <tr>
                                                    <td><?= e($jadwal['hari']) ?></td>
                                                    <td><?= e(substr($jadwal['jam_mulai'], 0, 5)) ?>–<?= e(substr($jadwal['jam_selesai'], 0, 5)) ?></td>
                                                    <td><?= e($jadwal['ruangan']) ?></td>
                                                    <td><?= (int)$jadwal['apakah_lab'] === 1 ? 'Lab/Bengkel' : 'Teori' ?></td>
                                                    <?php if ($semesterDapatDisiapkan): ?>
                                                        <td>
                                                            <form method="post" action="<?= BASE_URL ?>/kesiswaan/tahun-ajaran/jadwal/hapus" onsubmit="return confirm('Hapus jadwal ini?')">
                                                                <?= \App\Helpers\Session::csrfField() ?>
                                                                <input type="hidden" name="id_tahun" value="<?= (int)$tahunTerpilih['id'] ?>">
                                                                <input type="hidden" name="id_semester" value="<?= (int)$semesterTerpilih['id'] ?>">
                                                                <input type="hidden" name="id_kelas" value="<?= (int)$kelasTerpilih['id'] ?>">
                                                                <input type="hidden" name="id_jadwal" value="<?= (int)$jadwal['id'] ?>">
                                                                <button class="btn btn-outline" type="submit">Hapus</button>
                                                            </form>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($semesterDapatDisiapkan && $templateKurikulum): ?>
                <div style="margin-top: 1.25rem;">
                    <h4>Template mapel di tingkat ini</h4>
                    <div style="display: flex; flex-wrap: wrap; gap: .5rem;">
                        <?php foreach ($templateKurikulum as $template): ?>
                            <form method="post" action="<?= BASE_URL ?>/kesiswaan/tahun-ajaran/template/hapus" onsubmit="return confirm('Hapus template <?= e($template['nama_mapel']) ?>?')">
                                <?= \App\Helpers\Session::csrfField() ?>
                                <input type="hidden" name="id_tahun" value="<?= (int)$tahunTerpilih['id'] ?>">
                                <input type="hidden" name="id_semester" value="<?= (int)$semesterTerpilih['id'] ?>">
                                <input type="hidden" name="id_kelas" value="<?= (int)$kelasTerpilih['id'] ?>">
                                <input type="hidden" name="id_template" value="<?= (int)$template['id'] ?>">
                                <button class="btn btn-outline" type="submit"><?= e($template['nama_mapel']) ?> &bull; Hapus</button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($periodeAktif && $tahunTerpilih && $semesterTerpilih
    && (int)$tahunTerpilih['id'] === (int)$periodeAktif['id_tahun_ajaran']
    && $periodeAktif['jenis_semester'] === 'ganjil'
    && $semesterTerpilih['jenis_semester'] === 'genap'
): ?>
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <h3>Aktifkan Semester Genap</h3>
            <p>Perpindahan ini hanya mengganti semester di tahun ajaran yang sama. Tidak ada siswa yang dinaikkan kelas.</p>
            <?php if ($kesiapanSemester && $kesiapanSemester['siap']): ?>
                <form method="post" action="<?= BASE_URL ?>/kesiswaan/tahun-ajaran/aktifkan-genap" onsubmit="return confirm('Aktifkan Semester Genap? Kenaikan kelas tidak dilakukan pada langkah ini.')">
                    <?= \App\Helpers\Session::csrfField() ?>
                    <input type="hidden" name="id_tahun" value="<?= (int)$tahunTerpilih['id'] ?>">
                    <input type="hidden" name="id_semester" value="<?= (int)$semesterTerpilih['id'] ?>">
                    <input type="hidden" name="id_kelas" value="<?= (int)($kelasTerpilih['id'] ?? 0) ?>">
                    <button class="btn btn-primary" type="submit">Aktifkan Semester Genap</button>
                </form>
            <?php else: ?>
                <p class="text-muted">Lengkapi semua persyaratan semester terlebih dahulu.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($pratinjauKenaikan): ?>
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <h3>Finalisasi Tahun Ajaran & Kenaikan Kelas</h3>
            <p>Langkah tahunan hanya tersedia setelah Semester Genap aktif. Kelas 10/11 naik satu tingkat pada nomor rombel yang sama; kelas 12 lulus. Nilai, absensi, dan penempatan lama tetap tersimpan.</p>
            <?php if ($pratinjauKenaikan['siap']): ?>
                <p><strong><?= (int)$pratinjauKenaikan['naik'] ?></strong> siswa akan naik kelas dan <strong><?= (int)$pratinjauKenaikan['lulus'] ?></strong> siswa kelas 12 akan diluluskan.</p>
            <?php else: ?>
                <div class="alert alert-warning"><?= e($pratinjauKenaikan['message']) ?></div>
            <?php endif; ?>
            <?php foreach ($kesiapanTahunBaru as $kesiapan): ?>
                <div style="margin: .75rem 0; padding: .75rem; border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                    <strong>Semester <?= ucfirst(e($kesiapan['semester']['jenis_semester'] ?? '')) ?>:</strong>
                    <?= $kesiapan['siap'] ? 'siap' : 'belum siap' ?>
                    <?php if (!$kesiapan['siap']): ?>
                        <ul>
                            <?php foreach (array_slice($kesiapan['errors'], 0, 8) as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php
            $semuaSemesterSiap = count($kesiapanTahunBaru) === 2;
            foreach ($kesiapanTahunBaru as $kesiapan) {
                $semuaSemesterSiap = $semuaSemesterSiap && $kesiapan['siap'];
            }
            ?>
            <?php if ($pratinjauKenaikan['siap'] && $semuaSemesterSiap): ?>
                <form method="post" action="<?= BASE_URL ?>/kesiswaan/tahun-ajaran/finalisasi-kenaikan" onsubmit="return confirm('Lanjutkan kenaikan kelas? Siswa kelas 10/11 akan dipindahkan ke tahun berikutnya dan siswa kelas 12 akan diluluskan. Proses ini tidak dapat dibatalkan.')">
                    <?= \App\Helpers\Session::csrfField() ?>
                    <input type="hidden" name="id_tahun" value="<?= (int)$tahunTerpilih['id'] ?>">
                    <input type="hidden" name="id_semester" value="<?= (int)($semesterTerpilih['id'] ?? 0) ?>">
                    <input type="hidden" name="id_kelas" value="<?= (int)($kelasTerpilih['id'] ?? 0) ?>">
                    <button class="btn btn-primary" type="submit">Finalisasi & Aktifkan Tahun Baru</button>
                </form>
            <?php else: ?>
                <p class="text-muted">Tahun baru belum dapat diaktifkan. Pastikan kedua semester siap dan preview kenaikan valid.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
