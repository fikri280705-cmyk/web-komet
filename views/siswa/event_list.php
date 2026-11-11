<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/siswa/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Mading Sekolah</span>
        </div>
        <h1 class="page-title">Event & Mading Digital Sekolah</h1>
        <p class="page-subtitle">Informasi kegiatan siswa, ekstrakurikuler, dan agenda resmi SMK Negeri 9 Kabupaten Tangerang</p>
    </div>
</div>

<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <a href="<?= BASE_URL ?>/siswa/event" class="btn btn-sm <?= empty($filterKategori) ? 'btn-primary' : 'btn-outline' ?>">Semua Kategori</a>
            <?php foreach ($daftarKategori as $kat): ?>
                <a href="<?= BASE_URL ?>/siswa/event?kategori=<?= (int)$kat['id'] ?>" class="btn btn-sm <?= ($filterKategori == $kat['id']) ? 'btn-primary' : 'btn-outline' ?>">
                    <?= e($kat['nama_kategori']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div style="position: relative; min-width: 240px;">
            <input type="text" id="cariEvent" class="form-control" placeholder="Cari event, lokasi..." style="font-size: 13px;" onkeyup="filterEventCards()">
        </div>
    </div>

    <div class="card-body">
        <?php if (empty($daftarEvent)): ?>
            <div style="padding: 3.5rem 1.5rem; text-align: center; color: var(--color-text-muted);">
                <div style="width: 60px; height: 60px; margin: 0 auto 1rem; border-radius: 50%; background: #F1F5F9; display: flex; align-items: center; justify-content: center; color: var(--color-text-muted);">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                </div>
                <h4 style="font-weight: 700; color: var(--color-text-main); font-size: 15px;">Belum Ada Event Terpublikasi</h4>
                <p style="font-size: 13px; margin-top: 0.25rem;">Nantikan pengumuman agenda dan acara seru lainnya di SMKN 9 Kabupaten Tangerang.</p>
            </div>
        <?php else: ?>
            <div id="eventGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                <?php foreach ($daftarEvent as $evt): ?>
                    <div class="card event-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--color-border); box-shadow: var(--shadow-sm);">
                        <div>
                            <?php if (!empty($evt['foto_poster'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/event/<?= e($evt['foto_poster']) ?>" alt="Poster" style="width: 100%; height: 180px; object-fit: cover; border-top-left-radius: var(--radius-lg); border-top-right-radius: var(--radius-lg); background: #0F172A;" onerror="this.src='<?= BASE_URL ?>/assets/images/logosmkn9.png';">
                            <?php else: ?>
                                <div style="width: 100%; height: 140px; background: linear-gradient(135deg, var(--color-primary), var(--color-secondary)); display: flex; align-items: center; justify-content: center; color: #FFFFFF; border-top-left-radius: var(--radius-lg); border-top-right-radius: var(--radius-lg);">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                </div>
                            <?php endif; ?>

                            <div style="padding: 1.25rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span class="badge badge-info"><?= e($evt['nama_kategori']) ?></span>
                                    <span style="font-size: 11.5px; color: var(--color-text-muted); font-weight: 500;">
                                        <?= date('d M Y', strtotime($evt['waktu_mulai'])) ?>
                                    </span>
                                </div>
                                <h3 style="font-size: 16px; font-weight: 800; color: var(--color-text-main); margin-bottom: 0.5rem; line-height: 1.3;" class="event-title">
                                    <?= e($evt['judul']) ?>
                                </h3>
                                <p style="font-size: 12.5px; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 1rem;">
                                    <?= nl2br(e($evt['deskripsi'])) ?>
                                </p>
                            </div>
                        </div>

                        <div style="padding: 0.85rem 1.25rem; background: var(--color-bg-subtle, #f8fafc); border-top: 1px solid var(--color-border); font-size: 12px; color: var(--color-text-body); display: flex; flex-direction: column; gap: 4px;">
                            <div>📍 <strong>Lokasi:</strong> <span class="event-lokasi"><?= e($evt['lokasi']) ?></span></div>
                            <div>⏰ <strong>Waktu:</strong> <?= date('H:i', strtotime($evt['waktu_mulai'])) ?> - <?= date('H:i', strtotime($evt['waktu_selesai'])) ?> WIB</div>
                            <div>👥 <strong>Penyelenggara:</strong> <?= e($evt['penyelenggara'] ?? 'OSIS') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function filterEventCards() {
    const filter = document.getElementById('cariEvent').value.toLowerCase();
    const cards = document.querySelectorAll('#eventGrid .event-card');
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(filter) ? 'flex' : 'none';
    });
}
</script>

