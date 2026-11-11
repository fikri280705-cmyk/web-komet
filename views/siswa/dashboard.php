<!-- Hero Banner Siswa -->
<div class="hero-banner">
    <div class="hero-banner-content">
        <div class="hero-banner-tag">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
            </svg>
            <span>NIS: <?= e($siswa['nis']) ?> &bull; Kelas <?= e($siswa['nama_kelas'] ?? 'Siswa') ?> (<?= e($siswa['kode_jurusan']) ?>)</span>
        </div>
        <h1>Halo, <?= e($siswa['nama_lengkap']) ?>! 👋</h1>
        <p>Selamat datang di Portal Akademik SIAKAD SMKN 9 Kabupaten Tangerang. Pantau hasil belajar, jadwal KBM kejuruan, dan agenda kegiatan sekolah.</p>
    </div>
</div>

<!-- Kartu Ringkasan Siswa -->
<div class="grid-3" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= count($nilaiFinal) ?> Mapel</h3>
            <p>Nilai Rapor Final Terbit</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon accent">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= count($jadwalKelas) ?> Sesi KBM</h3>
            <p>Jadwal Kelas <?= e($siswa['nama_kelas'] ?? '') ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
        </div>
        <div class="stat-info">
            <h3><?= count($eventTerbaru) ?> Event</h3>
            <p>Agenda & Mading Terkini</p>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 2rem;">
    <!-- Nilai Rapor Terkini -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-primary);">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                Nilai Rapor Terverifikasi
            </h3>
            <a href="<?= BASE_URL ?>/siswa/nilai" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($nilaiFinal)): ?>
                <div style="padding: 2.5rem 1.5rem; text-align: center; color: var(--color-text-muted);">
                    <p style="font-size: 13px;">Belum ada nilai final yang disahkan.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mata Pelajaran</th>
                                <th style="text-align: center;">Nilai Akhir</th>
                                <th style="text-align: center;">Predikat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nilaiFinal as $nf): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--color-text-main); font-size: 13px;"><?= e($nf['nama_mapel']) ?></div>
                                        <div style="font-size: 11px; color: var(--color-text-muted);"><?= e($nf['nama_guru_pengajar']) ?></div>
                                    </td>
                                    <td style="text-align: center; font-weight: 800; color: var(--color-primary); font-size: 14px;">
                                        <?= number_format($nf['nilai_akhir'], 1) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= in_array($nf['predikat_huruf'], ['A', 'B']) ? 'badge-success' : 'badge-warning' ?>" style="font-weight: 800; font-size: 12px;">
                                            <?= e($nf['predikat_huruf']) ?>
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

    <!-- Jadwal Belajar Saya Preview -->
    <div class="card">
        <div class="card-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-accent);">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                Jadwal Belajar Saya
            </h3>
            <a href="<?= BASE_URL ?>/siswa/jadwal" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($jadwalKelas)): ?>
                <div style="padding: 2.5rem 1.5rem; text-align: center; color: var(--color-text-muted);">
                    <p style="font-size: 13px;">Belum ada jadwal KBM kelas Anda.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Hari & Jam</th>
                                <th>Mata Pelajaran</th>
                                <th>Ruangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jadwalKelas as $jdw): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--color-text-main); font-size: 13px;"><?= e($jdw['hari']) ?></div>
                                        <div style="font-size: 11px; color: var(--color-text-muted); font-family: monospace;">
                                            <?= substr($jdw['jam_mulai'], 0, 5) ?> - <?= substr($jdw['jam_selesai'], 0, 5) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--color-text-main); font-size: 13px;"><?= e($jdw['nama_mapel']) ?></div>
                                        <div style="font-size: 11px; color: var(--color-text-muted);"><?= e($jdw['nama_guru']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge badge-neutral"><?= e($jdw['ruangan']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-secondary);">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            Event & Mading Sekolah Terbaru
        </h3>
        <a href="<?= BASE_URL ?>/siswa/event" class="btn btn-outline btn-sm">Buka Menu &rarr;</a>
    </div>
    <div class="card-body">
        <?php if (empty($eventTerbaru)): ?>
            <div style="text-align: center; padding: 2.5rem 1rem; color: var(--color-text-muted);">
                <p style="font-size: 13px;">Belum ada pengumuman kegiatan baru.</p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
                <?php foreach ($eventTerbaru as $evt): ?>
                    <div style="display: flex; gap: 1rem; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0.95rem; background: #FFFFFF;">
                        <?php if (!empty($evt['foto_poster'])): ?>
                            <img src="<?= BASE_URL ?>/uploads/event/<?= e($evt['foto_poster']) ?>" alt="Poster" style="width: 76px; height: 76px; object-fit: cover; border-radius: var(--radius-sm); flex-shrink: 0; background: #0F172A; border: 1px solid var(--color-border);" onerror="this.src='<?= BASE_URL ?>/assets/images/logosmkn9.png';">
                        <?php else: ?>
                            <div style="width: 76px; height: 76px; background: rgba(17, 46, 129, 0.08); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--color-primary); flex-shrink: 0;">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                </svg>
                            </div>
                        <?php endif; ?>

                        <div style="flex: 1; min-width: 0;">
                            <span class="badge badge-info" style="font-size: 10px; padding: 1px 6px;"><?= e($evt['nama_kategori']) ?></span>
                            <h4 style="font-size: 13px; font-weight: 700; color: var(--color-text-main); margin: 4px 0 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= e($evt['judul']) ?>
                            </h4>
                            <div style="font-size: 11px; color: var(--color-text-muted);">
                                📍 <?= e($evt['lokasi']) ?><br>
                                📅 <?= date('d M Y', strtotime($evt['waktu_mulai'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
