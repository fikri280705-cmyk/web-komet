<?php
/**
 * Controller Dasbor Kaprog (Kepala Program Keahlian)
 * Terisolasi ketat pada Jurusan yang diampu
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Helpers\Session;
use App\Helpers\Audit;

class KaprogController extends Controller {
    private Jurusan $modelJurusan;
    private Kelas $modelKelas;
    private Siswa $modelSiswa;
    private Guru $modelGuru;
    private Jadwal $modelJadwal;

    public function __construct() {
        $this->modelJurusan = new Jurusan();
        $this->modelKelas = new Kelas();
        $this->modelSiswa = new Siswa();
        $this->modelGuru = new Guru();
        $this->modelJadwal = new Jadwal();
    }

    private function getJurusanKaprog(): array {
        $idPengguna = Session::get('pengguna_id');
        $jurusan = $this->modelJurusan->cariBerdasarkanIdPenggunaKaprog($idPengguna);

        if (!$jurusan) {
            // Fallback default jika admin login sebagai demo
            $daftar = $this->modelJurusan->ambilSemua();
            return $daftar[0] ?? ['id' => 1, 'kode_jurusan' => 'TKJ', 'nama_jurusan' => 'Teknik Komputer dan Jaringan'];
        }

        return $jurusan;
    }

    public function dashboard(): void {
        $jurusan = $this->getJurusanKaprog();
        $idJurusan = (int)$jurusan['id'];

        $daftarKelas = $this->modelKelas->ambilBerdasarkanJurusan($idJurusan);
        $daftarSiswa = $this->modelSiswa->ambilBerdasarkanJurusan($idJurusan);
        $jadwalJurusan = $this->modelJadwal->ambilBerdasarkanJurusan($idJurusan);
        $daftarGuru = $this->modelGuru->ambilBerdasarkanJurusan($idJurusan);

        $data = [
            'judulHalaman'  => "Dashboard Kaprog {$jurusan['nama_jurusan']}",
            'user'          => Session::user(),
            'jurusan'       => $jurusan,
            'daftarKelas'   => $daftarKelas,
            'totalSiswa'    => count($daftarSiswa),
            'jadwalJurusan' => array_slice($jadwalJurusan, 0, 5),
            'previewSiswa'  => array_slice($daftarSiswa, 0, 5),
            'previewGuru'   => array_slice($daftarGuru, 0, 5),
        ];

        $this->render('kaprog/dashboard', $data);
    }

    /**
     * Halaman Siswa Khusus Jurusan Kaprog
     */
    public function siswaJurusan(): void {
        $jurusan = $this->getJurusanKaprog();
        $idJurusan = (int)$jurusan['id'];
        $idKelas = !empty($_GET['kelas']) ? (int)$_GET['kelas'] : null;

        $daftarKelas = $this->modelKelas->ambilBerdasarkanJurusan($idJurusan);
        $daftarSiswa = $this->modelSiswa->ambilSemua($idJurusan, $idKelas, 200);

        $data = [
            'judulHalaman' => "Data Siswa Jurusan {$jurusan['nama_jurusan']}",
            'user'         => Session::user(),
            'jurusan'      => $jurusan,
            'daftarKelas'  => $daftarKelas,
            'daftarSiswa'  => $daftarSiswa,
            'filterKelas'  => $idKelas
        ];

        $this->render('kaprog/siswa_list', $data);
    }

    /**
     * Halaman Guru & Wali Kelas Jurusan Kaprog
     */
    public function guruJurusan(): void {
        $jurusan = $this->getJurusanKaprog();
        $idJurusan = (int)$jurusan['id'];

        $daftarGuru = $this->modelGuru->ambilBerdasarkanJurusan($idJurusan);
        $daftarKelas = $this->modelKelas->ambilBerdasarkanJurusan($idJurusan);

        $data = [
            'judulHalaman' => "Guru & Wali Kelas Jurusan {$jurusan['nama_jurusan']}",
            'user'         => Session::user(),
            'jurusan'      => $jurusan,
            'daftarGuru'   => $daftarGuru,
            'daftarKelas'  => $daftarKelas
        ];

        $this->render('kaprog/guru_list', $data);
    }

    /**
     * Tetapkan Wali Kelas untuk Kelas di Jurusan
     */
    public function tetapkanWaliKelas(): void {
        $idGuru = (int)($_POST['id_guru'] ?? 0);
        $idKelas = (int)($_POST['id_kelas'] ?? 0);
        $idJurusan = (int)$this->getJurusanKaprog()['id'];

        if ($idGuru && $idKelas) {
            $guru = $this->modelGuru->cariBerdasarkanId($idGuru);
            $kelas = $this->modelKelas->cariBerdasarkanId($idKelas);

            if ($guru && $kelas
                && ($guru['id_jurusan'] === null || (int)$guru['id_jurusan'] === $idJurusan)
                && (int)$kelas['id_jurusan'] === $idJurusan) {
                if ($this->modelGuru->tetapkanWaliKelas($idGuru, $idKelas)) {
                    $this->modelGuru->tetapkanPeranGuru($idGuru, 'wali_kelas');
                    Audit::catat('ASSIGN_WALI_KELAS', 'kelas', $idKelas, null, ['id_guru' => $idGuru]);
                    Session::setFlash('success', 'Penugasan Wali Kelas berhasil diperbarui!');
                } else {
                    Session::setFlash('error', 'Guru sudah menjadi wali kelas di kelas lain, atau tahun ajaran aktif belum tersedia.');
                }
            } else {
                Session::setFlash('error', 'Guru dan kelas harus berasal dari jurusan yang Anda kelola.');
            }
        } else {
            Session::setFlash('error', 'Pilih guru dan kelas dengan benar.');
        }

        $this->redirect('/kaprog/guru');
    }

    /**
     * Halaman Kelas Jurusan Kaprog
     */
    public function kelasJurusan(): void {
        $jurusan = $this->getJurusanKaprog();
        $idJurusan = (int)$jurusan['id'];

        $daftarKelas = $this->modelKelas->ambilBerdasarkanJurusan($idJurusan);

        $data = [
            'judulHalaman' => "Daftar Kelas Jurusan {$jurusan['nama_jurusan']}",
            'user'         => Session::user(),
            'jurusan'      => $jurusan,
            'daftarKelas'  => $daftarKelas
        ];

        $this->render('kaprog/kelas_list', $data);
    }

    /**
     * Halaman Jadwal KBM Jurusan Kaprog
     */
    public function jadwalKbm(): void {
        $jurusan = $this->getJurusanKaprog();
        $idJurusan = (int)$jurusan['id'];
        $idKelas = isset($_GET['kelas'])
            ? filter_var($_GET['kelas'], FILTER_VALIDATE_INT)
            : null;

        $daftarKelas = $this->modelKelas->ambilBerdasarkanJurusan($idJurusan);
        $kelasJadwal = null;
        if ($idKelas === false || ($idKelas !== null && $idKelas < 1)) {
            Session::setFlash('error', 'Kelas tidak ditemukan.');
            $this->redirect('/kaprog/kelas');
            return;
        }

        if ($idKelas !== null) {
            $kelasJadwal = $this->modelKelas->cariBerdasarkanId($idKelas);
            if (!$kelasJadwal || (int)$kelasJadwal['id_jurusan'] !== $idJurusan) {
                Session::setFlash('error', 'Kelas tidak ditemukan pada jurusan yang Anda kelola.');
                $this->redirect('/kaprog/kelas');
                return;
            }
            $jadwalJurusan = $this->modelJadwal->ambilBerdasarkanKelas($idKelas);
        } else {
            $jadwalJurusan = $this->modelJadwal->ambilBerdasarkanJurusan($idJurusan);
        }

        $data = [
            'judulHalaman'  => $kelasJadwal
                ? "Jadwal KBM Kelas {$kelasJadwal['nama_kelas']}"
                : "Jadwal KBM Jurusan {$jurusan['nama_jurusan']}",
            'user'          => Session::user(),
            'jurusan'       => $jurusan,
            'daftarKelas'   => $daftarKelas,
            'jadwalJurusan' => $jadwalJurusan,
            'kelasJadwal'   => $kelasJadwal
        ];

        $this->render('kaprog/jadwal_list', $data);
    }

    /**
     * Halaman Jadwal Lab & Bengkel Jurusan Kaprog
     */
    public function jadwalLab(): void {
        $jurusan = $this->getJurusanKaprog();
        $idJurusan = (int)$jurusan['id'];

        $jadwalLab = $this->modelJadwal->ambilJadwalLabBerdasarkanJurusan($idJurusan);

        $data = [
            'judulHalaman' => "Jadwal Penggunaan Lab & Bengkel - {$jurusan['nama_jurusan']}",
            'user'         => Session::user(),
            'jurusan'      => $jurusan,
            'jadwalLab'    => $jadwalLab
        ];

        $this->render('kaprog/jadwal_lab', $data);
    }
}
