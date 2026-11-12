<?php
/**
 * FRONT CONTROLLER - SISTEM INFORMASI AKADEMIK (SIAKAD) SMK NEGERI 9 KABUPATEN TANGERANG
 * Satu-satunya titik masuk publik (Single Entry Point)
 */

define('AKSES_SIAKAD', true);

// Muat konfigurasi dasar aplikasi dan inisialisasi sesi
require_once dirname(__DIR__) . '/app/Config/App.php';

// PSR-4 Autoloader Sederhana untuk Namespace App\...
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = PATH_APP . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Muat Helper Global
require_once PATH_APP . '/Helpers/Sanitizer.php';
require_once PATH_APP . '/Helpers/functions.php';

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\KesiswaanController;
use App\Controllers\TahunAjaranController;
use App\Controllers\KaprogController;
use App\Controllers\WaliKelasController;
use App\Controllers\SiswaController;
use App\Controllers\OsisController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\CsrfMiddleware;

$router = new Router();

// ==========================================
// 1. RUTE PUBLIK & OTENTIKASI
// ==========================================
$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'prosesLogin'], [CsrfMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);

// ==========================================
// 2. RUTE PENGATURAN AKUN & PROFIL (SEMUA ROLE)
// ==========================================
$router->get('/pengaturan-akun', [AuthController::class, 'pengaturanAkun'], [AuthMiddleware::class]);
$router->post('/pengaturan-akun/password', [AuthController::class, 'updateKataSandi'], [
    AuthMiddleware::class,
    CsrfMiddleware::class
]);
$router->get('/profil/detail', [AuthController::class, 'detailProfil'], [AuthMiddleware::class]);
$router->post('/profil/detail/simpan', [AuthController::class, 'simpanDetailProfil'], [
    AuthMiddleware::class,
    CsrfMiddleware::class
]);
$router->post('/profil/foto/upload', [AuthController::class, 'uploadFotoProfil'], [
    AuthMiddleware::class,
    CsrfMiddleware::class
]);

// ==========================================
// 3. RUTE KESISWAAN (ADMIN GLOBAL)
// ==========================================
$router->get('/kesiswaan/dashboard', [KesiswaanController::class, 'dashboard'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan'])
]);
$router->get('/kesiswaan/siswa', [KesiswaanController::class, 'dataSiswa'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan'])
]);
$router->post('/kesiswaan/siswa/simpan', [KesiswaanController::class, 'simpanSiswa'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/siswa/tambah', [KesiswaanController::class, 'tambahSiswa'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/siswa/hapus', [KesiswaanController::class, 'hapusSiswa'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->get('/kesiswaan/guru', [KesiswaanController::class, 'dataGuru'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan'])
]);
$router->post('/kesiswaan/guru/simpan', [KesiswaanController::class, 'simpanGuru'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/guru/tambah', [KesiswaanController::class, 'tambahGuru'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/guru/hapus', [KesiswaanController::class, 'hapusGuru'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->get('/kesiswaan/moderasi-event', [KesiswaanController::class, 'moderasiEvent'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan'])
]);
$router->post('/kesiswaan/event/setujui', [KesiswaanController::class, 'setujuiEvent'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/event/tolak', [KesiswaanController::class, 'tolakEvent'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->get('/kesiswaan/jurusan', [KesiswaanController::class, 'konsentrasiKeahlian'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan'])
]);
$router->get('/kesiswaan/kelas', [KesiswaanController::class, 'kelasJurusan'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan'])
]);
$router->get('/kesiswaan/jadwal', [KesiswaanController::class, 'jadwalKbm'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan'])
]);
$router->get('/kesiswaan/tahun-ajaran', [TahunAjaranController::class, 'index'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan'])
]);
$router->get('/kesiswaan/tahun-ajaran/impor/template', [TahunAjaranController::class, 'unduhTemplateImpor'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan'])
]);
$router->post('/kesiswaan/tahun-ajaran/impor', [TahunAjaranController::class, 'imporPersiapan'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/tahun-ajaran/buat-draft', [TahunAjaranController::class, 'buatDraft'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/tahun-ajaran/template/simpan', [TahunAjaranController::class, 'simpanTemplate'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/tahun-ajaran/template/hapus', [TahunAjaranController::class, 'hapusTemplate'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/tahun-ajaran/wali/simpan', [TahunAjaranController::class, 'simpanWali'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/tahun-ajaran/penugasan/simpan', [TahunAjaranController::class, 'simpanPenugasan'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/tahun-ajaran/penugasan/hapus', [TahunAjaranController::class, 'hapusPenugasan'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/tahun-ajaran/jadwal/simpan', [TahunAjaranController::class, 'simpanJadwal'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/tahun-ajaran/jadwal/hapus', [TahunAjaranController::class, 'hapusJadwal'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/tahun-ajaran/aktifkan-genap', [TahunAjaranController::class, 'aktifkanGenap'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);
$router->post('/kesiswaan/tahun-ajaran/finalisasi-kenaikan', [TahunAjaranController::class, 'finalisasiKenaikan'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kesiswaan']),
    CsrfMiddleware::class
]);

// ==========================================
// 4. RUTE KAPROG (KEPALA PROGRAM JURUSAN)
// ==========================================
$router->get('/kaprog/dashboard', [KaprogController::class, 'dashboard'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kaprog'])
]);
$router->get('/kaprog/siswa', [KaprogController::class, 'siswaJurusan'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kaprog'])
]);
$router->get('/kaprog/guru', [KaprogController::class, 'guruJurusan'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kaprog'])
]);
$router->post('/kaprog/guru/wali-kelas', [KaprogController::class, 'tetapkanWaliKelas'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kaprog']),
    CsrfMiddleware::class
]);
$router->get('/kaprog/kelas', [KaprogController::class, 'kelasJurusan'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kaprog'])
]);
$router->get('/kaprog/jadwal', [KaprogController::class, 'jadwalKbm'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kaprog'])
]);
$router->get('/kaprog/jadwal-lab', [KaprogController::class, 'jadwalLab'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['kaprog'])
]);

// ==========================================
// 5. RUTE WALI KELAS (ROMBEL BINAAN)
// ==========================================
$router->get('/wali-kelas/dashboard', [WaliKelasController::class, 'dashboard'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['wali_kelas'])
]);
$router->get('/wali-kelas/siswa', [WaliKelasController::class, 'siswaBinaan'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['wali_kelas'])
]);
$router->post('/wali-kelas/siswa/presensi', [WaliKelasController::class, 'simpanPresensiKelas'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['wali_kelas']),
    CsrfMiddleware::class
]);
$router->get('/wali-kelas/jadwal', [WaliKelasController::class, 'jadwalPelajaran'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['wali_kelas'])
]);
$router->get('/wali-kelas/nilai', [WaliKelasController::class, 'inputNilai'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['wali_kelas'])
]);
$router->post('/wali-kelas/nilai/simpan', [WaliKelasController::class, 'simpanNilai'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['wali_kelas']),
    CsrfMiddleware::class
]);
$router->post('/wali-kelas/nilai/perbarui', [WaliKelasController::class, 'perbaruiNilaiSiswa'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['wali_kelas']),
    CsrfMiddleware::class
]);
$router->get('/wali-kelas/absensi', [WaliKelasController::class, 'rekapAbsensi'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['wali_kelas'])
]);
$router->post('/wali-kelas/absensi/simpan', [WaliKelasController::class, 'simpanAbsensi'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['wali_kelas']),
    CsrfMiddleware::class
]);
$router->post('/wali-kelas/absensi/perbarui', [WaliKelasController::class, 'perbaruiAbsensiSiswa'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['wali_kelas']),
    CsrfMiddleware::class
]);

// ==========================================
// 6. RUTE SISWA (PORTAL RAPOR & JADWAL)
// ==========================================
$router->get('/siswa/dashboard', [SiswaController::class, 'dashboard'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['siswa'])
]);
$router->get('/siswa/jadwal', [SiswaController::class, 'jadwalBelajar'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['siswa'])
]);
$router->get('/siswa/nilai', [SiswaController::class, 'nilaiRapor'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['siswa'])
]);
$router->get('/siswa/event', [SiswaController::class, 'eventSekolah'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['siswa'])
]);

// ==========================================
// 7. RUTE ASISTEN SEKOLAH (OSIS)
// ==========================================
$router->get('/osis/dashboard', [OsisController::class, 'dashboard'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['osis'])
]);
$router->get('/osis/event', [OsisController::class, 'kelolaEvent'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['osis'])
]);
$router->get('/osis/event/baru', [OsisController::class, 'formBuatEvent'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['osis'])
]);
$router->post('/osis/event/simpan', [OsisController::class, 'simpanEvent'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['osis']),
    CsrfMiddleware::class
]);
$router->get('/osis/pengurus', [OsisController::class, 'strukturPengurus'], [
    AuthMiddleware::class,
    RoleMiddleware::izinkan(['osis'])
]);

// Jalankan Dispatcher
$router->dispatch();
