<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>/osis/dashboard" class="badge badge-neutral" style="text-decoration: none;">&larr; Kembali ke Dashboard</a>
            <span class="badge badge-primary">Manajemen Event</span>
        </div>
        <h1 class="page-title">Kelola Event & Mading Sekolah</h1>
        <p class="page-subtitle">Daftar agenda kegiatan, jadwal pelaksanaan, dan status kurasi persetujuan Kesiswaan</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/osis/event/baru" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>+ Buat Event Baru</span>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <a href="<?= BASE_URL ?>/osis/event" class="btn btn-sm <?= empty($filterStatus) ? 'btn-primary' : 'btn-outline' ?>">Semua Status</a>
            <a href="<?= BASE_URL ?>/osis/event?status=draf" class="btn btn-sm <?= ($filterStatus === 'draf') ? 'btn-primary' : 'btn-outline' ?>">Draf</a>
            <a href="<?= BASE_URL ?>/osis/event?status=menunggu_persetujuan" class="btn btn-sm <?= ($filterStatus === 'menunggu_persetujuan') ? 'btn-primary' : 'btn-outline' ?>">Menunggu Review</a>
            <a href="<?= BASE_URL ?>/osis/event?status=dipublikasikan" class="btn btn-sm <?= ($filterStatus === 'dipublikasikan') ? 'btn-primary' : 'btn-outline' ?>">Publikasi</a>
        </div>
        <div style="position: relative; min-width: 240px;">
            <input type="text" id="cariEvent" class="form-control" placeholder="Cari event, lokasi..." style="font-size: 13px;" onkeyup="filterEvent()">
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="data-table" id="tabelEvent">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="cursor: pointer; user-select: none;" onclick="sortTable(1)">
                            Judul Kegiatan <span class="sort-icon" style="font-size: 10px; opacity: 0.6;">⇅</span>
                        </th>
                        <th style="cursor: pointer; user-select: none;" onclick="sortTable(2)">
                            Kategori <span class="sort-icon" style="font-size: 10px; opacity: 0.6;">⇅</span>
                        </th>
                        <th style="cursor: pointer; user-select: none;" onclick="sortTable(3)">
                            Waktu Pelaksanaan <span class="sort-icon" style="font-size: 10px; opacity: 0.6;">⇅</span>
                        </th>
                        <th style="cursor: pointer; user-select: none;" onclick="sortTable(4)">
                            Tanggal Diajukan <span class="sort-icon" style="font-size: 10px; opacity: 0.6;">⇅</span>
                        </th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarEvent)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-text-muted);">
                                Belum ada data event pada filter ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $idx = 1; foreach ($daftarEvent as $evt): ?>
                            <tr class="baris-event">
                                <td style="text-align: center; color: var(--color-text-muted); font-weight: 600;"><?= $idx++ ?></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-text-main); font-size: 13.5px;"><?= e($evt['judul']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 2px;">
                                        📍 <?= e($evt['lokasi']) ?> &bull; Penyelenggara: <?= e($evt['penyelenggara'] ?? 'OSIS') ?>
                                    </div>
                                    <?php if (!empty($evt['alasan_penolakan'])): ?>
                                        <div style="font-size: 11px; color: var(--color-danger); margin-top: 4px;">
                                            Catatan: <?= e($evt['alasan_penolakan']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= e($evt['nama_kategori']) ?></span>
                                </td>
                                <td data-value="<?= strtotime($evt['waktu_mulai']) ?>">
                                    <div style="font-weight: 600; font-size: 12.5px; color: var(--color-text-body);">
                                        <?= date('d M Y', strtotime($evt['waktu_mulai'])) ?>
                                    </div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);">
                                        <?= date('H:i', strtotime($evt['waktu_mulai'])) ?> - <?= date('H:i', strtotime($evt['waktu_selesai'])) ?> WIB
                                    </div>
                                </td>
                                <td data-value="<?= strtotime($evt['dibuat_pada']) ?>">
                                    <div style="font-size: 12px; color: var(--color-text-body); font-weight: 500;">
                                        <?= date('d M Y H:i', strtotime($evt['dibuat_pada'])) ?>
                                    </div>
                                    <div style="font-size: 11px; color: var(--color-text-muted);">
                                        Oleh: <?= e($evt['nama_pembuat'] ?? 'OSIS') ?>
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
                                        <span class="badge badge-neutral">Draf</span>
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

<script>
let sortAsc = true;
function sortTable(columnIndex) {
    const table = document.getElementById('tabelEvent');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr.baris-event'));
    
    sortAsc = !sortAsc;
    
    rows.sort((a, b) => {
        let valA = a.children[columnIndex].getAttribute('data-value') || a.children[columnIndex].textContent.trim();
        let valB = b.children[columnIndex].getAttribute('data-value') || b.children[columnIndex].textContent.trim();
        
        if (!isNaN(valA) && !isNaN(valB)) {
            return sortAsc ? valA - valB : valB - valA;
        }
        return sortAsc ? valA.localeCompare(valB) : valB.localeCompare(valA);
    });

    rows.forEach((row, i) => {
        row.children[0].textContent = i + 1;
        tbody.appendChild(row);
    });
}

function filterEvent() {
    const filter = document.getElementById('cariEvent').value.toLowerCase();
    const rows = document.querySelectorAll('#tabelEvent .baris-event');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>

