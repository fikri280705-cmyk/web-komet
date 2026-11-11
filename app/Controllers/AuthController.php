<?php
/**
 * Controller Otentikasi & Akun Pengguna
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Pengguna;
use App\Models\Siswa;
use App\Models\Guru;
use App\Helpers\Session;
use App\Helpers\Audit;

class AuthController extends Controller {
    private Pengguna $modelPengguna;
    private Siswa $modelSiswa;
    private Guru $modelGuru;

    public function __construct() {
        $this->modelPengguna = new Pengguna();
        $this->modelSiswa = new Siswa();
        $this->modelGuru = new Guru();
    }

    /**
     * Tampilkan Halaman Login
     */
    public function showLogin(): void {
        if (Session::isLoggedIn()) {
            $this->redirect('/' . Session::role() . '/dashboard');
            return;
        }

        $this->renderPartial('auth/login', [
            'judulHalaman' => 'Masuk - SIAKAD SMKN 9 Kabupaten Tangerang'
        ]);
    }

    /**
     * Proses Submit Login
     */
    public function prosesLogin(): void {
        $nomorInduk = trim($_POST['nomor_induk'] ?? ($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($nomorInduk) || empty($password)) {
            Session::setFlash('error', 'Silakan isi NIS atau NUPTK dan kata sandi.');
            $this->redirect('/login');
            return;
        }

        $pengguna = $this->modelPengguna->cariBerdasarkanKredensial($nomorInduk);

        if (!$pengguna || !$this->modelPengguna->verifikasiKataSandi($password, $pengguna['kata_sandi'])) {
            Audit::catat('LOGIN_GAGAL', 'pengguna', null, null, ['input' => $nomorInduk]);
            Session::setFlash('error', 'Kredensial tidak valid atau akun dinonaktifkan.');
            $this->redirect('/login');
            return;
        }

        // Login Berhasil - Regenerasi ID Sesi
        session_regenerate_id(true);

        Session::set('pengguna_id', $pengguna['id']);
        Session::set('nomor_induk', $pengguna['nomor_induk']);
        Session::set('nama_lengkap', $pengguna['nama_lengkap']);
        Session::set('email', $pengguna['email']);
        Session::set('id_peran', $pengguna['id_peran']);
        Session::set('kode_peran', $pengguna['kode_peran']);
        Session::set('nama_peran', $pengguna['nama_peran']);
        Session::set('foto_profil', $pengguna['foto_profil'] ?: 'avatar_default.png');
        Session::set('user_agent_hash', md5($_SERVER['HTTP_USER_AGENT'] ?? ''));

        Audit::catat('LOGIN_BERHASIL', 'pengguna', $pengguna['id']);
        Session::setFlash('success', "Selamat datang kembali, {$pengguna['nama_lengkap']}!");

        // Redirect sesuai peran masing-masing
        $roleRoutes = [
            'kesiswaan' => '/kesiswaan/dashboard',
            'kaprog'    => '/kaprog/dashboard',
            'wali_kelas'=> '/wali-kelas/dashboard',
            'siswa'     => '/siswa/dashboard',
            'osis'      => '/osis/dashboard',
        ];

        $targetUrl = $roleRoutes[$pengguna['kode_peran']] ?? '/login';
        $this->redirect($targetUrl);
    }

    /**
     * Proses Keluar Sesi (Logout)
     */
    public function logout(): void {
        if (Session::isLoggedIn()) {
            Audit::catat('LOGOUT', 'pengguna', Session::get('pengguna_id'));
        }

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    /**
     * Halaman Pengaturan Akun & Ganti Password
     */
    public function pengaturanAkun(): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $idPengguna = Session::get('pengguna_id');
        $role = Session::role();
        
        $detailAkun = null;
        if ($role === 'siswa' || $role === 'osis') {
            $detailAkun = $this->modelSiswa->cariBerdasarkanIdPengguna($idPengguna);
        } else {
            $detailAkun = $this->modelGuru->cariBerdasarkanIdPengguna($idPengguna);
        }

        $this->render('auth/pengaturan', [
            'judulHalaman' => 'Pengaturan Akun & Keamanan',
            'user'         => Session::user(),
            'detailAkun'   => $detailAkun
        ]);
    }

    /**
     * Halaman Ubah Detail Profil Lengkap
     */
    public function detailProfil(): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $currentUserId = Session::get('pengguna_id');
        $currentUserRole = Session::role();

        // Kesiswaan dapat melihat/mengedit profil pengguna lain via ?id_pengguna=X
        $targetUserId = $currentUserId;
        if ($currentUserRole === 'kesiswaan' && !empty($_GET['id_pengguna'])) {
            $targetUserId = (int)$_GET['id_pengguna'];
        }

        $pengguna = $this->modelPengguna->cariBerdasarkanId($targetUserId);
        if (!$pengguna) {
            Session::setFlash('error', 'Data pengguna tidak ditemukan.');
            $this->redirect('/pengaturan-akun');
            return;
        }

        $targetRole = $pengguna['kode_peran'];
        $detailAkun = null;
        if ($targetRole === 'siswa' || $targetRole === 'osis') {
            $detailAkun = $this->modelSiswa->cariBerdasarkanIdPengguna($targetUserId);
        } else {
            $detailAkun = $this->modelGuru->cariBerdasarkanIdPengguna($targetUserId);
        }

        $isKesiswaan = ($currentUserRole === 'kesiswaan');

        $this->render('auth/profil_detail', [
            'judulHalaman' => 'Ubah Detail Data Profil',
            'user'         => Session::user(),
            'pengguna'     => $pengguna,
            'detailAkun'   => $detailAkun,
            'isKesiswaan'  => $isKesiswaan,
            'isSelf'       => ($targetUserId === $currentUserId)
        ]);
    }

    /**
     * Simpan Perubahan Detail Data Profil
     */
    public function simpanDetailProfil(): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $currentUserId = Session::get('pengguna_id');
        $currentUserRole = Session::role();

        $targetUserId = $currentUserId;
        if ($currentUserRole === 'kesiswaan' && !empty($_POST['id_pengguna'])) {
            $targetUserId = (int)$_POST['id_pengguna'];
        }

        $pengguna = $this->modelPengguna->cariBerdasarkanId($targetUserId);
        if (!$pengguna) {
            Session::setFlash('error', 'Pengguna tidak valid.');
            $this->redirect('/pengaturan-akun');
            return;
        }

        $isKesiswaan = ($currentUserRole === 'kesiswaan');
        $targetRole = $pengguna['kode_peran'];

        if ($targetRole === 'siswa' || $targetRole === 'osis') {
            $siswa = $this->modelSiswa->cariBerdasarkanIdPengguna($targetUserId);
            if ($siswa) {
                $dataUpdate = [
                    'nama_lengkap'      => $isKesiswaan ? trim($_POST['nama_lengkap'] ?? $siswa['nama_lengkap']) : $siswa['nama_lengkap'],
                    'nis'               => $isKesiswaan ? trim($_POST['nis'] ?? $siswa['nis']) : $siswa['nis'],
                    'nisn'              => $isKesiswaan ? trim($_POST['nisn'] ?? $siswa['nisn']) : $siswa['nisn'],
                    'jenis_kelamin'     => $isKesiswaan ? ($_POST['jenis_kelamin'] ?? $siswa['jenis_kelamin']) : $siswa['jenis_kelamin'],
                    'tempat_lahir'      => trim($_POST['tempat_lahir'] ?? $siswa['tempat_lahir']),
                    'tanggal_lahir'     => $_POST['tanggal_lahir'] ?? $siswa['tanggal_lahir'],
                    'agama'             => $_POST['agama'] ?? $siswa['agama'],
                    'alamat'            => trim($_POST['alamat'] ?? $siswa['alamat']),
                    'provinsi'          => trim($_POST['provinsi'] ?? 'Banten'),
                    'kabupaten'         => trim($_POST['kabupaten'] ?? 'Kabupaten Tangerang'),
                    'kode_pos'          => trim($_POST['kode_pos'] ?? '15710'),
                    'nomor_telepon'     => trim($_POST['nomor_telepon'] ?? ''),
                    'nama_ayah'         => trim($_POST['nama_ayah'] ?? ''),
                    'nama_ibu'          => trim($_POST['nama_ibu'] ?? ''),
                    'pekerjaan_ortu'    => trim($_POST['pekerjaan_ortu'] ?? ''),
                    'telepon_orang_tua' => trim($_POST['telepon_orang_tua'] ?? ''),
                    'id_jurusan'        => $isKesiswaan ? (int)($_POST['id_jurusan'] ?? $siswa['id_jurusan']) : $siswa['id_jurusan'],
                    'id_kelas'          => $isKesiswaan ? (int)($_POST['id_kelas'] ?? 0) : 0,
                    'status_siswa'      => $isKesiswaan ? ($_POST['status_siswa'] ?? $siswa['status_siswa']) : $siswa['status_siswa'],
                ];
                $this->modelSiswa->perbaruiData($siswa['id'], $dataUpdate);
            }
        } else {
            $guru = $this->modelGuru->cariBerdasarkanIdPengguna($targetUserId);
            if ($guru) {
                $dataUpdate = [
                    'nama_lengkap'        => $isKesiswaan ? trim($_POST['nama_lengkap'] ?? $guru['nama_lengkap']) : $guru['nama_lengkap'],
                    'nuptk'               => $isKesiswaan ? trim($_POST['nuptk'] ?? $guru['nuptk']) : $guru['nuptk'],
                    'nip'                 => $isKesiswaan ? trim($_POST['nip'] ?? $guru['nip']) : $guru['nip'],
                    'jenis_kelamin'       => $isKesiswaan ? ($_POST['jenis_kelamin'] ?? $guru['jenis_kelamin']) : $guru['jenis_kelamin'],
                    'tempat_lahir'        => trim($_POST['tempat_lahir'] ?? $guru['tempat_lahir']),
                    'tanggal_lahir'       => $_POST['tanggal_lahir'] ?? $guru['tanggal_lahir'],
                    'agama'               => $_POST['agama'] ?? $guru['agama'],
                    'nomor_telepon'       => trim($_POST['nomor_telepon'] ?? ''),
                    'email'               => trim($_POST['email'] ?? ''),
                    'alamat'              => trim($_POST['alamat'] ?? ''),
                    'provinsi'            => trim($_POST['provinsi'] ?? 'Banten'),
                    'kabupaten'           => trim($_POST['kabupaten'] ?? 'Kabupaten Tangerang'),
                    'kode_pos'            => trim($_POST['kode_pos'] ?? '15710'),
                    'pendidikan_terakhir' => trim($_POST['pendidikan_terakhir'] ?? $guru['pendidikan_terakhir']),
                    'npwp'                => trim($_POST['npwp'] ?? ''),
                    'id_jurusan'          => $isKesiswaan ? (!empty($_POST['id_jurusan']) ? (int)$_POST['id_jurusan'] : null) : $guru['id_jurusan'],
                    'mapel_utama'         => trim($_POST['mapel_utama'] ?? $guru['mapel_utama']),
                    'jabatan_tugas'       => $isKesiswaan ? ($_POST['jabatan_tugas'] ?? $guru['jabatan_tugas']) : $guru['jabatan_tugas'],
                ];
                $this->modelGuru->perbaruiData($guru['id'], $dataUpdate);
            }
        }

        Session::setFlash('success', 'Detail data profil berhasil diperbarui!');
        $redirectUrl = ($targetUserId === $currentUserId) ? '/profil/detail' : "/profil/detail?id_pengguna={$targetUserId}";
        $this->redirect($redirectUrl);
    }

    /**
     * Upload & Perbarui Foto Profil
     */
    public function uploadFotoProfil(): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $currentUserId = Session::get('pengguna_id');
        $currentUserRole = Session::role();

        $targetUserId = $currentUserId;
        if ($currentUserRole === 'kesiswaan' && !empty($_POST['id_pengguna'])) {
            $targetUserId = (int)$_POST['id_pengguna'];
        }

        if (empty($_FILES['foto_profil']['name'])) {
            Session::setFlash('error', 'Silakan pilih berkas foto untuk diunggah.');
            $this->redirect('/pengaturan-akun');
            return;
        }

        $file = $_FILES['foto_profil'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Terjadi kesalahan saat mengunggah foto.');
            $this->redirect('/pengaturan-akun');
            return;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            Session::setFlash('error', 'Ukuran foto maksimal 2 MB.');
            $this->redirect('/pengaturan-akun');
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            Session::setFlash('error', 'Format berkas harus JPG, PNG, atau WebP.');
            $this->redirect('/pengaturan-akun');
            return;
        }

        $namaFile = "avatar_user_{$targetUserId}_" . time() . ".{$ext}";
        $dest = PATH_UPLOADS . '/profil/' . $namaFile;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $this->modelPengguna->perbaruiFotoProfil($targetUserId, $namaFile);
            if ($targetUserId === $currentUserId) {
                Session::set('foto_profil', $namaFile);
            }
            Session::setFlash('success', 'Foto profil berhasil diperbarui!');
        } else {
            Session::setFlash('error', 'Gagal memindahkan berkas foto.');
        }

        $redirectUrl = ($targetUserId === $currentUserId) ? '/pengaturan-akun' : "/profil/detail?id_pengguna={$targetUserId}";
        $this->redirect($redirectUrl);
    }

    /**
     * Proses Ubah Kata Sandi
     */
    public function updateKataSandi(): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $idPengguna = Session::get('pengguna_id');
        $passLama = $_POST['password_lama'] ?? '';
        $passBaru = $_POST['password_baru'] ?? '';
        $passKonf = $_POST['password_konfirmasi'] ?? '';

        if (empty($passLama) || empty($passBaru) || empty($passKonf)) {
            Session::setFlash('error', 'Semua kolom kata sandi wajib diisi.');
            $this->redirect('/pengaturan-akun');
            return;
        }

        if (strlen($passBaru) < 8) {
            Session::setFlash('error', 'Kata sandi baru minimal harus 8 karakter.');
            $this->redirect('/pengaturan-akun');
            return;
        }

        if ($passBaru !== $passKonf) {
            Session::setFlash('error', 'Konfirmasi kata sandi baru tidak cocok.');
            $this->redirect('/pengaturan-akun');
            return;
        }

        $pengguna = $this->modelPengguna->cariBerdasarkanId($idPengguna);
        if (!$this->modelPengguna->verifikasiKataSandi($passLama, $pengguna['kata_sandi'])) {
            Session::setFlash('error', 'Kata sandi saat ini keliru.');
            $this->redirect('/pengaturan-akun');
            return;
        }

        $this->modelPengguna->perbaruiKataSandi($idPengguna, $passBaru);
        Audit::catat('GANTI_PASSWORD', 'pengguna', $idPengguna);
        Session::setFlash('success', 'Kata sandi Anda berhasil diperbarui!');
        $this->redirect('/pengaturan-akun');
    }
}
