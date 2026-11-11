<?php
/**
 * Controller Dasbor OSIS & Unggah Foto Poster Event
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Event;
use App\Models\Osis;
use App\Helpers\Session;
use App\Helpers\Audit;

class OsisController extends Controller {
    private Event $modelEvent;
    private Osis $modelOsis;

    public function __construct() {
        $this->modelEvent = new Event();
        $this->modelOsis = new Osis();
    }

    public function dashboard(): void {
        $idPengguna = Session::get('pengguna_id');
        $eventSaya = $this->modelEvent->ambilEventSaya($idPengguna);
        $pengurusOsis = $this->modelOsis->ambilSemuaPengurus();

        $draf = array_filter($eventSaya, fn($e) => $e['status'] === 'draf');
        $pending = array_filter($eventSaya, fn($e) => $e['status'] === 'menunggu_persetujuan');
        $published = array_filter($eventSaya, fn($e) => $e['status'] === 'dipublikasikan');
        $archived = array_filter($eventSaya, fn($e) => $e['status'] === 'diarsipkan');

        $data = [
            'judulHalaman' => 'Pusat Event & Mading OSIS - SMKN 9 Kabupaten Tangerang',
            'user'         => Session::user(),
            'eventSaya'    => $eventSaya,
            'pengurusOsis' => array_slice($pengurusOsis, 0, 5),
            'totalDraf'    => count($draf),
            'totalPending' => count($pending),
            'totalPublik'  => count($published),
            'totalArsip'   => count($archived),
        ];

        $this->render('osis/dashboard', $data);
    }

    /**
     * Halaman Kelola Event & Mading Sekolah
     */
    public function kelolaEvent(): void {
        $idPengguna = Session::get('pengguna_id');
        $status = $_GET['status'] ?? null;
        $daftarEvent = $this->modelEvent->ambilSemua($status);

        $data = [
            'judulHalaman' => 'Kelola Event & Mading Sekolah',
            'user'         => Session::user(),
            'daftarEvent'  => $daftarEvent,
            'filterStatus' => $status
        ];

        $this->render('osis/event_list', $data);
    }

    /**
     * Halaman Struktur Pengurus OSIS
     */
    public function strukturPengurus(): void {
        $daftarPengurus = $this->modelOsis->ambilSemuaPengurus();

        $data = [
            'judulHalaman'   => 'Struktur Pengurus OSIS - SMKN 9 Kabupaten Tangerang',
            'user'           => Session::user(),
            'daftarPengurus' => $daftarPengurus
        ];

        $this->render('osis/pengurus_list', $data);
    }

    /**
     * Halaman Formulir Buat Event Baru
     */
    public function formBuatEvent(): void {
        $data = [
            'judulHalaman' => 'Buat Usulan Event Sekolah Baru',
            'user'         => Session::user(),
        ];
        $this->render('osis/form_event', $data);
    }

    /**
     * Proses Simpan Event & Unggah Foto Poster
     */
    public function simpanEvent(): void {
        $idPengguna = Session::get('pengguna_id');
        $judul = trim($_POST['judul'] ?? '');
        $idKategori = (int)($_POST['id_kategori'] ?? 3);
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $lokasi = trim($_POST['lokasi'] ?? 'SMKN 9 Kabupaten Tangerang');
        $waktuMulai = $_POST['waktu_mulai'] ?? date('Y-m-d H:i:s');
        $waktuSelesai = $_POST['waktu_selesai'] ?? date('Y-m-d H:i:s');
        $penyelenggara = trim($_POST['penyelenggara'] ?? 'OSIS SMKN 9 Kabupaten Tangerang');
        $aksiSubmit = $_POST['aksi_submit'] ?? 'draf';

        if (empty($judul) || empty($deskripsi)) {
            Session::setFlash('error', 'Judul dan deskripsi kegiatan wajib diisi.');
            $this->redirect('/osis/event/baru');
            return;
        }

        // Penanganan Unggah Foto Poster
        $namaBerkasPoster = null;
        if (!empty($_FILES['foto_poster']['name'])) {
            $file = $_FILES['foto_poster'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                if ($file['size'] > 2 * 1024 * 1024) {
                    Session::setFlash('error', 'Ukuran foto poster melebihi batas maksimal 2 MB.');
                    $this->redirect('/osis/event/baru');
                    return;
                }

                $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($ekstensi, $ekstensiValid, true)) {
                    Session::setFlash('error', 'Format foto poster tidak valid. Hanya JPG, PNG, atau WebP yang diperbolehkan.');
                    $this->redirect('/osis/event/baru');
                    return;
                }

                $namaBerkasPoster = 'poster_' . bin2hex(random_bytes(10)) . '.' . $ekstensi;
                $tujuan = PATH_UPLOADS . '/event/' . $namaBerkasPoster;

                if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
                    Session::setFlash('error', 'Gagal memindahkan foto poster ke direktori penyimpanan server.');
                    $this->redirect('/osis/event/baru');
                    return;
                }
            }
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $judul)));
        $slugUnik = $slug . '-' . time();
        $status = ($aksiSubmit === 'ajukan') ? 'menunggu_persetujuan' : 'draf';

        $idEventBaru = $this->modelEvent->buatEvent(
            $judul,
            $slugUnik,
            $idKategori,
            $deskripsi,
            $lokasi,
            $waktuMulai,
            $waktuSelesai,
            $namaBerkasPoster,
            $penyelenggara,
            $status,
            $idPengguna
        );

        Audit::catat('BUAT_EVENT', 'event_sekolah', $idEventBaru, null, ['judul' => $judul, 'status' => $status]);

        $pesan = ($status === 'menunggu_persetujuan') 
            ? 'Usulan event berhasil diajukan dan sedang menunggu verifikasi Kesiswaan!' 
            : 'Draf event berhasil disimpan!';

        Session::setFlash('success', $pesan);
        $this->redirect('/osis/event');
    }
}
