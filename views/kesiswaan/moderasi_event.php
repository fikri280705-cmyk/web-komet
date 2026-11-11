<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/kesiswaan/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-warning">Publikasi & Mading</span>
        </div>
        <h1 class="page-title">Moderasi Event & Kegiatan OSIS</h1>
        <p class="page-subtitle">Verifikasi, persetujuan, dan pengawasan agenda kegiatan siswa serta publikasi mading sekolah</p>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/kesiswaan/moderasi-event" class="btn btn-sm <?= empty($filterStatus) ? 'btn-primary' : 'btn-outline' ?>">Semua Event</a>
            <a href="<?= BASE_URL ?>/kesiswaan/moderasi-event?status=menunggu_persetujuan" class="btn btn-sm <?= ($filterStatus === 'menunggu_persetujuan') ? 'btn-primary' : 'btn-outline' ?>">Menunggu Review</a>
            <a href="<?= BASE_URL ?>/kesiswaan/moderasi-event?status=dipublikasikan" class="btn btn-sm <?= ($filterStatus === 'dipublikasikan') ? 'btn-primary' : 'btn-outline' ?>">Dipublikasikan</a>
            <a href="<?= BASE_URL ?>/kesiswaan/moderasi-event?status=draf" class="btn btn-sm <?= ($filterStatus === 'draf') ? 'btn-primary' : 'btn-outline' ?>">Draf / Perlu Revisi</a>
        </div>
        <div>
            <span class="badge badge-neutral">Total: <?= count($daftarEvent) ?> Agenda</span>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Judul & Agenda Kegiatan</th>
                        <th>Pengusul / OSIS</th>
                        <th>Kategori</th>
                        <th>Jadwal Pelaksanaan</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center; width: 170px;">Tindakan Moderasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarEvent)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                                Tidak ada agenda event dalam filter ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $idx = 1; foreach ($daftarEvent as $evt): ?>
                            <tr>
                                <td style="text-align: center; color: var(--color-text-muted); font-weight: 600;"><?= $idx++ ?></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-text-main); font-size: 13.5px;"><?= e($evt['judul']) ?></div>
                                    <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 3px;">
                                        📍 <?= e($evt['lokasi']) ?> &bull; Penyelenggara: <?= e($evt['penyelenggara'] ?? 'OSIS') ?>
                                    </div>
                                    <?php if (!empty($evt['alasan_penolakan'])): ?>
                                        <div style="font-size: 11.5px; color: var(--color-danger); margin-top: 4px; background: #FEF2F2; padding: 3px 8px; border-radius: 4px; display: inline-block;">
                                            Catatan Revisi: <?= e($evt['alasan_penolakan']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 13px; color: var(--color-text-body);"><?= e($evt['nama_pembuat'] ?? '-') ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);"><?= date('d M Y H:i', strtotime($evt['dibuat_pada'])) ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= e($evt['nama_kategori']) ?></span>
                                </td>
                                <td>
                                    <div style="font-size: 12.5px; font-weight: 600; color: var(--color-text-body);">
                                        <?= date('d M Y', strtotime($evt['waktu_mulai'])) ?>
                                    </div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);">
                                        <?= date('H:i', strtotime($evt['waktu_mulai'])) ?> - <?= date('H:i', strtotime($evt['waktu_selesai'])) ?> WIB
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($evt['status'] === 'dipublikasikan'): ?>
                                        <span class="badge badge-success">
                                            <span class="badge-dot"></span> Dipublikasikan
                                        </span>
                                    <?php elseif ($evt['status'] === 'menunggu_persetujuan'): ?>
                                        <span class="badge badge-warning">
                                            <span class="badge-dot"></span> Menunggu Review
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">Draf / Revisi</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($evt['status'] === 'menunggu_persetujuan'): ?>
                                        <div style="display: flex; gap: 4px; justify-content: center;">
                                            <button type="button" class="btn btn-sm btn-primary" style="padding: 4px 8px; font-size: 11.5px;" data-id="<?= (int)$evt['id'] ?>" data-judul="<?= e($evt['judul']) ?>" onclick="bukaModalSetujui(this)">Setujui</button>
                                            <button type="button" class="btn btn-sm btn-outline" style="padding: 4px 8px; font-size: 11.5px; color: var(--color-danger); border-color: var(--color-danger);" data-id="<?= (int)$evt['id'] ?>" data-judul="<?= e($evt['judul']) ?>" onclick="bukaModalTolak(this)">Revisi</button>
                                        </div>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: var(--color-text-muted);">- Selesai -</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Persetujuan -->
<div id="modalSetujui" class="modal-overlay" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="judulModalSetujui" onclick="tutupModalJikaKlikLatar(event, 'modalSetujui')">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="judulModalSetujui">Setujui Usulan Event</h3>
            <button type="button" onclick="tutupModalSetujui()" aria-label="Tutup">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/kesiswaan/event/setujui" method="POST">
            <?= \App\Helpers\Session::csrfField() ?>
            <input type="hidden" id="setujuiIdEvent" name="id_event" value="">
            <div class="modal-body">
                <p>Setujui dan publikasikan event berikut?</p>
                <p id="setujuiJudulEvent" class="modal-event-title"></p>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="tutupModalSetujui()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Ya, Setujui</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tolak / Revisi -->
<div id="modalTolak" class="modal-overlay" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="judulModalTolak" onclick="tutupModalJikaKlikLatar(event, 'modalTolak')">
    <div class="modal-box" style="max-width: 440px;">
        <div class="modal-header">
            <h3 id="judulModalTolak">Catatan Revisi Usulan Event</h3>
            <button type="button" onclick="tutupModalTolak()" aria-label="Tutup">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/kesiswaan/event/tolak" method="POST">
            <?= \App\Helpers\Session::csrfField() ?>
            <input type="hidden" id="tolakIdEvent" name="id_event" value="">
            <div class="modal-body">
                <p id="tolakJudulEvent" style="font-weight: 600; font-size: 13px; margin-bottom: 0.75rem; color: var(--color-primary);"></p>
                <div class="form-group">
                    <label class="form-label">Alasan Pengembalian / Poin Perbaikan:</label>
                    <textarea name="alasan_penolakan" class="form-control" rows="3" required placeholder="Contoh: Lokasi bentrok dengan kegiatan sekolah lain atau deskripsi kurang lengkap"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="tutupModalTolak()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-sm" style="background: var(--color-danger); color: #fff;">Kirim Revisi</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalSetujui(tombol) {
    document.getElementById('setujuiIdEvent').value = tombol.dataset.id;
    document.getElementById('setujuiJudulEvent').textContent = tombol.dataset.judul;
    document.getElementById('modalSetujui').style.display = 'flex';
}
function tutupModalSetujui() {
    document.getElementById('modalSetujui').style.display = 'none';
}
function bukaModalTolak(tombol) {
    document.getElementById('tolakIdEvent').value = tombol.dataset.id;
    document.getElementById('tolakJudulEvent').textContent = 'Event: ' + tombol.dataset.judul;
    document.getElementById('modalTolak').style.display = 'flex';
    document.querySelector('#modalTolak textarea[name="alasan_penolakan"]').focus();
}
function tutupModalTolak() {
    document.getElementById('modalTolak').style.display = 'none';
}
function tutupModalJikaKlikLatar(event, modalId) {
    if (event.target.id === modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
}
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        tutupModalSetujui();
        tutupModalTolak();
    }
});
</script>
