<?php
/**
 * Controller Dasbor Kesiswaan & Kurikulum (Administrator Utama)
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Event;
use App\Models\Pesan;
use App\Models\Jadwal;
use App\Models\Pengguna;
use App\Helpers\Session;
use App\Helpers\Audit;

class KesiswaanController extends Controller {
    private Siswa $modelSiswa;
    private Guru $modelGuru;
    private Kelas $modelKelas;
    private Jurusan $modelJurusan;
    private Event $modelEvent;
    private Jadwal $modelJadwal;
    private Pengguna $modelPengguna;

    public function __construct() {
        $this->modelSiswa = new Siswa();
        $this->modelGuru = new Guru();
        $this->modelKelas = new Kelas();
        $this->modelJurusan = new Jurusan();
        $this->modelEvent = new Event();
        $this->modelJadwal = new Jadwal();
        $this->modelPengguna = new Pengguna();
    }

    public function dashboard(): void {
        $daftarGuru = $this->modelGuru->ambilSemua();
        $daftarSiswa = $this->modelSiswa->ambilSemua();

        $data = [
            'judulHalaman'      => 'Dashboard Kesiswaan & Kurikulum',
            'user'              => Session::user(),
            'totalSiswa'        => $this->modelSiswa->hitungTotal(),
            'totalGuru'         => $this->modelGuru->hitungTotal(),
            'daftarJurusan'     => $this->modelJurusan->ambilSemua(),
            'totalKelas'        => count($this->modelKelas->ambilSemua()),
            'eventPending'      => $this->modelEvent->ambilEventMenungguPersetujuan(),
            'previewGuru'       => array_slice($daftarGuru, 0, 5),
            'previewSiswa'      => array_slice($daftarSiswa, 0, 5),
        ];

        $this->render('kesiswaan/dashboard', $data);
    }

    /**
     * Halaman Data Induk Siswa Seluruh Sekolah
     */
    public function dataSiswa(): void {
        $idJurusan = !empty($_GET['jurusan']) ? (int)$_GET['jurusan'] : null;
        $idKelas = !empty($_GET['kelas']) ? (int)$_GET['kelas'] : null;
        $cari = !empty($_GET['cari']) ? trim($_GET['cari']) : null;

        $kelasTerpilih = null;
        if ($idKelas !== null) {
            $kelasTerpilih = $this->modelKelas->cariBerdasarkanId($idKelas);
            if (!$kelasTerpilih || ($idJurusan !== null && $idJurusan !== (int)$kelasTerpilih['id_jurusan'])) {
                Session::setFlash('error', 'Kelas tidak ditemukan pada jurusan yang dipilih.');
                $this->redirect('/kesiswaan/jurusan');
                return;
            }
            $idJurusan = (int)$kelasTerpilih['id_jurusan'];
        }

        // Ambil cukup banyak agar live search di klien mencakup hampir semua data
        $daftarSiswa = $this->modelSiswa->ambilSemua($idJurusan, $idKelas, 5000);

        $data = [
            'judulHalaman'  => $kelasTerpilih
                ? "Data Siswa Kelas {$kelasTerpilih['nama_kelas']}"
                : 'Data Induk Siswa - SMKN 9 Kabupaten Tangerang',
            'user'          => Session::user(),
            'daftarSiswa'   => $daftarSiswa,
            'daftarJurusan' => $this->modelJurusan->ambilSemua(),
            'daftarKelas'   => $this->modelKelas->ambilSemua(),
            'kelasTerpilih' => $kelasTerpilih,
            'filterCari'    => $cari,
            'filterJurusan' => $idJurusan,
            'filterKelas'   => $idKelas
        ];

        $this->render('kesiswaan/siswa_list', $data);
    }

    /**
     * Simpan Perubahan Siswa
     */
    public function simpanSiswa(): void {
        $idSiswa = (int)($_POST['id_siswa'] ?? 0);
        if (!$idSiswa) {
            Session::setFlash('error', 'Siswa tidak ditemukan.');
            $this->redirect('/kesiswaan/siswa');
            return;
        }

        $data = [
            'nama_lengkap'      => trim($_POST['nama_lengkap'] ?? ''),
            'nis'               => trim($_POST['nis'] ?? ''),
            'nisn'              => trim($_POST['nisn'] ?? ''),
            'jenis_kelamin'     => $_POST['jenis_kelamin'] ?? 'L',
            'tempat_lahir'      => trim($_POST['tempat_lahir'] ?? 'Tangerang'),
            'tanggal_lahir'     => $_POST['tanggal_lahir'] ?? null,
            'agama'             => $_POST['agama'] ?? 'Islam',
            'alamat'            => trim($_POST['alamat'] ?? ''),
            'provinsi'          => trim($_POST['provinsi'] ?? 'Banten'),
            'kabupaten'         => trim($_POST['kabupaten'] ?? 'Kabupaten Tangerang'),
            'kode_pos'          => trim($_POST['kode_pos'] ?? '15710'),
            'nomor_telepon'     => trim($_POST['nomor_telepon'] ?? ''),
            'nama_ayah'         => trim($_POST['nama_ayah'] ?? ''),
            'nama_ibu'          => trim($_POST['nama_ibu'] ?? ''),
            'pekerjaan_ortu'    => trim($_POST['pekerjaan_ortu'] ?? ''),
            'telepon_orang_tua' => trim($_POST['telepon_orang_tua'] ?? ''),
            'id_jurusan'        => (int)($_POST['id_jurusan'] ?? 0),
            'id_kelas'          => (int)($_POST['id_kelas'] ?? 0),
            'status_siswa'      => $_POST['status_siswa'] ?? 'aktif',
        ];

        $this->modelSiswa->perbaruiData($idSiswa, $data);
        Audit::catat('UPDATE_SISWA', 'siswa', $idSiswa);

        Session::setFlash('success', 'Data siswa berhasil diperbarui!');
        $this->redirect('/kesiswaan/siswa');
    }

    /**
     * Tambah Siswa Baru + akun login
     */
    public function tambahSiswa(): void {
        $data = [
            'nama_lengkap'      => trim($_POST['nama_lengkap'] ?? ''),
            'nis'               => trim($_POST['nis'] ?? ''),
            'nisn'              => trim($_POST['nisn'] ?? ''),
            'jenis_kelamin'     => $_POST['jenis_kelamin'] ?? 'L',
            'tempat_lahir'      => trim($_POST['tempat_lahir'] ?? 'Tangerang'),
            'tanggal_lahir'     => $_POST['tanggal_lahir'] ?: null,
            'agama'             => $_POST['agama'] ?? 'Islam',
            'alamat'            => trim($_POST['alamat'] ?? ''),
            'provinsi'          => trim($_POST['provinsi'] ?? 'Banten'),
            'kabupaten'         => trim($_POST['kabupaten'] ?? 'Kabupaten Tangerang'),
            'kode_pos'          => trim($_POST['kode_pos'] ?? '15710'),
            'nomor_telepon'     => trim($_POST['nomor_telepon'] ?? ''),
            'nama_ayah'         => trim($_POST['nama_ayah'] ?? ''),
            'nama_ibu'          => trim($_POST['nama_ibu'] ?? ''),
            'pekerjaan_ortu'    => trim($_POST['pekerjaan_ortu'] ?? ''),
            'telepon_orang_tua' => trim($_POST['telepon_orang_tua'] ?? ''),
            'id_jurusan'        => (int)($_POST['id_jurusan'] ?? 0),
            'id_kelas'          => (int)($_POST['id_kelas'] ?? 0),
            'tahun_masuk'       => (int)($_POST['tahun_masuk'] ?? date('Y')),
            'status_siswa'      => 'aktif',
        ];

        if ($data['nama_lengkap'] === '' || $data['nis'] === '' || $data['id_jurusan'] <= 0) {
            Session::setFlash('error', 'Nama, NIS, dan jurusan wajib diisi.');
            $this->redirect('/kesiswaan/siswa');
            return;
        }

        if ($this->modelSiswa->nisSudahAda($data['nis']) || $this->modelPengguna->nomorIndukSudahAda($data['nis'])) {
            Session::setFlash('error', 'NIS sudah terdaftar di sistem.');
            $this->redirect('/kesiswaan/siswa');
            return;
        }

        $idPeranSiswa = $this->modelPengguna->idPeranDariKode('siswa');
        if (!$idPeranSiswa) {
            Session::setFlash('error', 'Peran siswa tidak ditemukan di sistem.');
            $this->redirect('/kesiswaan/siswa');
            return;
        }

        $passwordPlain = 'smkn9#' . substr($data['nis'], -4);
        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            $email = 'siswa.' . $data['nis'] . '@smkn9kabtangerang.sch.id';
        }

        try {
            $idPengguna = $this->modelPengguna->buatAkun([
                'nomor_induk'   => $data['nis'],
                'email'         => $email,
                'kata_sandi'    => $passwordPlain,
                'id_peran'      => $idPeranSiswa,
                'nama_lengkap'  => $data['nama_lengkap'],
                'nomor_telepon' => $data['nomor_telepon'],
            ]);

            $idSiswa = $this->modelSiswa->buatBaru($data, $idPengguna);
            Audit::catat('TAMBAH_SISWA', 'siswa', $idSiswa, null, ['nis' => $data['nis']]);

            Session::setFlash(
                'success',
                "Siswa berhasil ditambahkan. Akun login: {$data['nis']} / kata sandi awal: {$passwordPlain}"
            );
        } catch (\Throwable $e) {
            error_log('Gagal tambah siswa: ' . $e->getMessage());
            Session::setFlash('error', 'Gagal menambahkan siswa. Periksa kembali data yang diisi.');
        }

        $this->redirect('/kesiswaan/siswa');
    }

    /**
     * Hapus Siswa beserta data terkait
     */
    public function hapusSiswa(): void {
        $idSiswa = (int)($_POST['id_siswa'] ?? 0);
        $siswa = $idSiswa ? $this->modelSiswa->cariBerdasarkanId($idSiswa) : null;

        if (!$siswa) {
            Session::setFlash('error', 'Siswa tidak ditemukan.');
            $this->redirect('/kesiswaan/siswa');
            return;
        }

        try {
            $idPengguna = $this->modelSiswa->hapus($idSiswa);
            $statusAkun = 'tanpa_akun';
            if ($idPengguna) {
                $statusAkun = $this->modelPengguna->hapusAtauNonaktifkan($idPengguna);
            }

            Audit::catat('HAPUS_SISWA', 'siswa', $idSiswa, ['nis' => $siswa['nis'], 'nama' => $siswa['nama_lengkap']]);
            Session::setFlash(
                'success',
                'Siswa ' . $siswa['nama_lengkap'] . ' berhasil dihapus'
                . ($statusAkun === 'dinonaktifkan' ? ' (akun login dinonaktifkan karena masih terkait data lain).' : '.')
            );
        } catch (\Throwable $e) {
            error_log('Gagal hapus siswa: ' . $e->getMessage());
            Session::setFlash('error', 'Gagal menghapus siswa.');
        }

        $this->redirect('/kesiswaan/siswa');
    }

    /**
     * Halaman Data Guru & Pegawai
     */
    public function dataGuru(): void {
        $daftarGuru = $this->modelGuru->ambilSemua();
        $daftarJurusan = $this->modelJurusan->ambilSemua();
        $daftarKelas = $this->modelKelas->ambilSemua();

        $data = [
            'judulHalaman'  => 'Data Guru & Pegawai - SMKN 9 Kabupaten Tangerang',
            'user'          => Session::user(),
            'daftarGuru'    => $daftarGuru,
            'daftarJurusan' => $daftarJurusan,
            'daftarKelas'   => $daftarKelas,
        ];

        $this->render('kesiswaan/guru_list', $data);
    }

    /**
     * Simpan Perubahan Data Guru
     */
    public function simpanGuru(): void {
        $idGuru = (int)($_POST['id_guru'] ?? 0);
        if (!$idGuru) {
            Session::setFlash('error', 'Guru tidak ditemukan.');
            $this->redirect('/kesiswaan/guru');
            return;
        }

        $data = [
            'nama_lengkap'        => trim($_POST['nama_lengkap'] ?? ''),
            'nuptk'               => trim($_POST['nuptk'] ?? ''),
            'nip'                 => trim($_POST['nip'] ?? ''),
            'jenis_kelamin'       => $_POST['jenis_kelamin'] ?? 'L',
            'tempat_lahir'        => trim($_POST['tempat_lahir'] ?? 'Tangerang'),
            'tanggal_lahir'       => $_POST['tanggal_lahir'] ?? null,
            'agama'               => $_POST['agama'] ?? 'Islam',
            'nomor_telepon'       => trim($_POST['nomor_telepon'] ?? ''),
            'email'               => trim($_POST['email'] ?? ''),
            'alamat'              => trim($_POST['alamat'] ?? ''),
            'provinsi'            => trim($_POST['provinsi'] ?? 'Banten'),
            'kabupaten'           => trim($_POST['kabupaten'] ?? 'Kabupaten Tangerang'),
            'kode_pos'            => trim($_POST['kode_pos'] ?? '15710'),
            'pendidikan_terakhir' => trim($_POST['pendidikan_terakhir'] ?? 'S1'),
            'npwp'                => trim($_POST['npwp'] ?? ''),
            'id_jurusan'          => !empty($_POST['id_jurusan']) ? (int)$_POST['id_jurusan'] : null,
            'mapel_utama'         => trim($_POST['mapel_utama'] ?? ''),
            'jabatan_tugas'       => $_POST['jabatan_tugas'] ?? 'Guru Pengajar',
        ];

        $this->modelGuru->perbaruiData($idGuru, $data);

        // Jika ada penugasan wali kelas
        if (!empty($_POST['id_kelas_wali'])) {
            $idKelas = (int)$_POST['id_kelas_wali'];
            if (!$this->modelGuru->tetapkanWaliKelas($idGuru, $idKelas)) {
                Session::setFlash('error', 'Guru sudah menjadi wali kelas di kelas lain, atau tahun ajaran aktif belum tersedia.');
                $this->redirect('/kesiswaan/guru');
                return;
            }
            $this->modelGuru->tetapkanPeranGuru($idGuru, 'wali_kelas');
        }

        // Upload foto profil jika ada
        if (!empty($_FILES['foto_guru']['name']) && $_FILES['foto_guru']['error'] === UPLOAD_ERR_OK) {
            $guru = $this->modelGuru->cariBerdasarkanId($idGuru);
            if ($guru && !empty($guru['id_pengguna'])) {
                $ext = strtolower(pathinfo($_FILES['foto_guru']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $namaFile = "avatar_user_{$guru['id_pengguna']}_" . time() . ".{$ext}";
                    $dest = PATH_UPLOADS . '/profil/' . $namaFile;
                    if (move_uploaded_file($_FILES['foto_guru']['tmp_name'], $dest)) {
                        $this->modelPengguna->perbaruiFotoProfil($guru['id_pengguna'], $namaFile);
                    }
                }
            }
        }

        Audit::catat('UPDATE_GURU', 'guru', $idGuru);
        Session::setFlash('success', 'Data guru dan penugasan berhasil diperbarui!');
        $this->redirect('/kesiswaan/guru');
    }

    /**
     * Tambah Guru Baru + akun login
     */
    public function tambahGuru(): void {
        $data = [
            'nama_lengkap'        => trim($_POST['nama_lengkap'] ?? ''),
            'nuptk'               => trim($_POST['nuptk'] ?? ''),
            'nip'                 => trim($_POST['nip'] ?? ''),
            'jenis_kelamin'       => $_POST['jenis_kelamin'] ?? 'L',
            'tempat_lahir'        => trim($_POST['tempat_lahir'] ?? 'Tangerang'),
            'tanggal_lahir'       => $_POST['tanggal_lahir'] ?: null,
            'agama'               => $_POST['agama'] ?? 'Islam',
            'nomor_telepon'       => trim($_POST['nomor_telepon'] ?? ''),
            'email'               => trim($_POST['email'] ?? ''),
            'alamat'              => trim($_POST['alamat'] ?? ''),
            'provinsi'            => trim($_POST['provinsi'] ?? 'Banten'),
            'kabupaten'           => trim($_POST['kabupaten'] ?? 'Kabupaten Tangerang'),
            'kode_pos'            => trim($_POST['kode_pos'] ?? '15710'),
            'pendidikan_terakhir' => trim($_POST['pendidikan_terakhir'] ?? 'S1 Kependidikan'),
            'npwp'                => trim($_POST['npwp'] ?? ''),
            'id_jurusan'          => !empty($_POST['id_jurusan']) ? (int)$_POST['id_jurusan'] : null,
            'mapel_utama'         => trim($_POST['mapel_utama'] ?? ''),
            'jabatan_tugas'       => $_POST['jabatan_tugas'] ?? 'Guru Pengajar',
        ];

        if ($data['nama_lengkap'] === '' || $data['nuptk'] === '') {
            Session::setFlash('error', 'Nama dan NUPTK wajib diisi.');
            $this->redirect('/kesiswaan/guru');
            return;
        }

        if ($this->modelGuru->nuptkSudahAda($data['nuptk']) || $this->modelPengguna->nomorIndukSudahAda($data['nuptk'])) {
            Session::setFlash('error', 'NUPTK sudah terdaftar di sistem.');
            $this->redirect('/kesiswaan/guru');
            return;
        }

        $kodePeran = 'wali_kelas';
        if ($data['jabatan_tugas'] === 'Kepala Program Keahlian') {
            $kodePeran = 'kaprog';
        } elseif ($data['jabatan_tugas'] === 'Kesiswaan & Kurikulum') {
            $kodePeran = 'kesiswaan';
        }

        $idPeran = $this->modelPengguna->idPeranDariKode($kodePeran);
        if (!$idPeran) {
            Session::setFlash('error', 'Peran guru tidak ditemukan di sistem.');
            $this->redirect('/kesiswaan/guru');
            return;
        }

        $passwordPlain = 'gurusmkn9#' . substr($data['nuptk'], -6);
        if ($data['email'] === '') {
            $slugNama = strtolower(preg_replace('/[^a-z0-9]+/i', '', explode(',', $data['nama_lengkap'])[0] ?? 'guru'));
            $data['email'] = ($slugNama ?: 'guru') . '@smkn9kabtangerang.sch.id';
        }

        try {
            $idPengguna = $this->modelPengguna->buatAkun([
                'nomor_induk'   => $data['nuptk'],
                'email'         => $data['email'],
                'kata_sandi'    => $passwordPlain,
                'id_peran'      => $idPeran,
                'nama_lengkap'  => $data['nama_lengkap'],
                'nomor_telepon' => $data['nomor_telepon'],
            ]);

            $idGuru = $this->modelGuru->buatBaru($data, $idPengguna);

            if (!empty($_POST['id_kelas_wali'])) {
                $idKelas = (int)$_POST['id_kelas_wali'];
                if ($this->modelGuru->tetapkanWaliKelas($idGuru, $idKelas)) {
                    $this->modelGuru->tetapkanPeranGuru($idGuru, 'wali_kelas');
                }
            }

            Audit::catat('TAMBAH_GURU', 'guru', $idGuru, null, ['nuptk' => $data['nuptk']]);
            Session::setFlash(
                'success',
                "Guru berhasil ditambahkan. Akun login: {$data['nuptk']} / kata sandi awal: {$passwordPlain}"
            );
        } catch (\Throwable $e) {
            error_log('Gagal tambah guru: ' . $e->getMessage());
            Session::setFlash('error', 'Gagal menambahkan guru. Periksa kembali data yang diisi.');
        }

        $this->redirect('/kesiswaan/guru');
    }

    /**
     * Hapus / nonaktifkan Guru
     */
    public function hapusGuru(): void {
        $idGuru = (int)($_POST['id_guru'] ?? 0);
        $guru = $idGuru ? $this->modelGuru->cariBerdasarkanId($idGuru) : null;

        if (!$guru) {
            Session::setFlash('error', 'Guru tidak ditemukan.');
            $this->redirect('/kesiswaan/guru');
            return;
        }

        $user = Session::user();
        if (!empty($user['id']) && !empty($guru['id_pengguna']) && (int)$user['id'] === (int)$guru['id_pengguna']) {
            Session::setFlash('error', 'Anda tidak dapat menghapus akun yang sedang dipakai.');
            $this->redirect('/kesiswaan/guru');
            return;
        }

        try {
            $hasil = $this->modelGuru->hapus($idGuru);
            if ($hasil['status'] === 'tidak_ditemukan') {
                Session::setFlash('error', 'Guru tidak ditemukan.');
                $this->redirect('/kesiswaan/guru');
                return;
            }

            if (!empty($hasil['id_pengguna'])) {
                if ($hasil['status'] === 'dinonaktifkan') {
                    $this->modelPengguna->hapusAtauNonaktifkan((int)$hasil['id_pengguna']);
                } else {
                    $this->modelPengguna->hapusAtauNonaktifkan((int)$hasil['id_pengguna']);
                }
            }

            Audit::catat('HAPUS_GURU', 'guru', $idGuru, [
                'nuptk' => $guru['nuptk'],
                'nama' => $guru['nama_lengkap'],
                'status' => $hasil['status'],
            ]);

            if ($hasil['status'] === 'dinonaktifkan') {
                Session::setFlash(
                    'success',
                    'Guru ' . $guru['nama_lengkap'] . ' dinonaktifkan karena masih memiliki penugasan mengajar.'
                );
            } else {
                Session::setFlash('success', 'Guru ' . $guru['nama_lengkap'] . ' berhasil dihapus.');
            }
        } catch (\Throwable $e) {
            error_log('Gagal hapus guru: ' . $e->getMessage());
            Session::setFlash('error', 'Gagal menghapus guru.');
        }

        $this->redirect('/kesiswaan/guru');
    }

    /**
     * Halaman Moderasi Usulan Event OSIS
     */
    public function moderasiEvent(): void {
        $status = $_GET['status'] ?? null;
        $daftarEvent = $this->modelEvent->ambilSemua($status);

        $data = [
            'judulHalaman' => 'Moderasi Event & Kegiatan OSIS',
            'user'         => Session::user(),
            'daftarEvent'  => $daftarEvent,
            'filterStatus' => $status,
        ];

        $this->render('kesiswaan/moderasi_event', $data);
    }

    /**
     * Setujui Usulan Event
     */
    public function setujuiEvent(): void {
        $idEvent = (int)($_POST['id_event'] ?? 0);
        if ($idEvent) {
            if ($this->modelEvent->setujuiEvent($idEvent, (int)Session::get('pengguna_id'))) {
                $event = $this->modelEvent->cariBerdasarkanId($idEvent);
                $ringkasan = ['db' => 0, 'wa_ok' => 0, 'wa_gagal' => 0];
                if ($event) {
                    $ringkasan = (new Pesan())->kirimOtomatisEventKeSemuaSiswa($event);
                }
                Session::setFlash(
                    'success',
                    'Event berhasil disetujui dan dipublikasikan di Mading Siswa!'
                    . " Notifikasi dibuat untuk {$ringkasan['db']} siswa."
                    . " WhatsApp: {$ringkasan['wa_ok']} terkirim/antrian, {$ringkasan['wa_gagal']} gagal."
                );
            } else {
                Session::setFlash('error', 'Event tidak ditemukan atau statusnya sudah berubah.');
            }
        } else {
            Session::setFlash('error', 'Event tidak ditemukan.');
        }
        $this->redirect('/kesiswaan/moderasi-event');
    }

    /**
     * Tolak Usulan Event
     */
    public function tolakEvent(): void {
        $idEvent = (int)($_POST['id_event'] ?? 0);
        $alasan = trim($_POST['alasan_penolakan'] ?? 'Perlu revisi data kegiatan.');
        if ($idEvent) {
            if ($this->modelEvent->tolakEvent($idEvent, (int)Session::get('pengguna_id'), $alasan)) {
                Session::setFlash('success', 'Usulan event telah dikembalikan ke pengurus OSIS dengan catatan revisi.');
            } else {
                Session::setFlash('error', 'Event tidak ditemukan atau statusnya sudah berubah.');
            }
        } else {
            Session::setFlash('error', 'Event tidak ditemukan.');
        }
        $this->redirect('/kesiswaan/moderasi-event');
    }

    /**
     * Halaman Konsentrasi Keahlian (Jurusan)
     */
    public function konsentrasiKeahlian(): void {
        $daftarJurusan = $this->modelJurusan->ambilSemua();
        $daftarKelas = $this->modelKelas->ambilSemua();
        
        $data = [
            'judulHalaman'  => 'Konsentrasi Keahlian - SMKN 9 Kabupaten Tangerang',
            'user'          => Session::user(),
            'daftarJurusan' => $daftarJurusan,
            'daftarKelas'   => $daftarKelas,
        ];

        $this->render('kesiswaan/jurusan_list', $data);
    }

    /**
     * Halaman Daftar Kelas berdasarkan Jurusan
     */
    public function kelasJurusan(): void {
        $idJurusan = filter_var($_GET['jurusan'] ?? null, FILTER_VALIDATE_INT);
        if ($idJurusan === false || $idJurusan === null || $idJurusan < 1) {
            Session::setFlash('error', 'Jurusan tidak ditemukan.');
            $this->redirect('/kesiswaan/jurusan');
            return;
        }

        $jurusan = $this->modelJurusan->cariBerdasarkanId($idJurusan);
        if (!$jurusan) {
            Session::setFlash('error', 'Jurusan tidak ditemukan.');
            $this->redirect('/kesiswaan/jurusan');
            return;
        }

        $data = [
            'judulHalaman' => "Daftar Kelas Jurusan {$jurusan['nama_jurusan']}",
            'user'         => Session::user(),
            'jurusan'      => $jurusan,
            'daftarKelas'  => $this->modelKelas->ambilBerdasarkanJurusan($idJurusan)
        ];

        $this->render('kesiswaan/kelas_list', $data);
    }

    /**
     * Halaman Jadwal KBM Semua Jurusan
     */
    public function jadwalKbm(): void {
        $idKelas = isset($_GET['kelas'])
            ? filter_var($_GET['kelas'], FILTER_VALIDATE_INT)
            : null;
        $kelasJadwal = null;
        if ($idKelas === false || ($idKelas !== null && $idKelas < 1)) {
            Session::setFlash('error', 'Kelas tidak ditemukan.');
            $this->redirect('/kesiswaan/jurusan');
            return;
        }

        if ($idKelas !== null) {
            $kelasJadwal = $this->modelKelas->cariBerdasarkanId($idKelas);
            if (!$kelasJadwal) {
                Session::setFlash('error', 'Kelas tidak ditemukan.');
                $this->redirect('/kesiswaan/jurusan');
                return;
            }
            $daftarJadwal = $this->modelJadwal->ambilBerdasarkanKelas($idKelas);
        } else {
            $daftarJadwal = $this->modelJadwal->ambilSemua();
        }
        $daftarJurusan = $this->modelJurusan->ambilSemua();
        $daftarKelas = $this->modelKelas->ambilSemua();

        $data = [
            'judulHalaman'  => $kelasJadwal
                ? "Jadwal KBM Kelas {$kelasJadwal['nama_kelas']}"
                : 'Jadwal KBM Semua Jurusan - SMKN 9 Kabupaten Tangerang',
            'user'          => Session::user(),
            'daftarJadwal'  => $daftarJadwal,
            'daftarJurusan' => $daftarJurusan,
            'daftarKelas'   => $daftarKelas,
            'kelasJadwal'   => $kelasJadwal,
        ];

        $this->render('kesiswaan/jadwal_list', $data);
    }
}
