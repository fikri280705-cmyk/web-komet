<?php
/**
 * Controller Portal Siswa
 * Terisolasi ketat pada Data Pribadi Siswa Sendiri
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Siswa;
use App\Models\Nilai;
use App\Models\Jadwal;
use App\Models\Event;
use App\Helpers\Session;

class SiswaController extends Controller {
    private Siswa $modelSiswa;
    private Nilai $modelNilai;
    private Jadwal $modelJadwal;
    private Event $modelEvent;

    public function __construct() {
        $this->modelSiswa = new Siswa();
        $this->modelNilai = new Nilai();
        $this->modelJadwal = new Jadwal();
        $this->modelEvent = new Event();
    }

    private function getProfilSiswa(): array {
        $idPengguna = Session::get('pengguna_id');
        $profil = $this->modelSiswa->cariBerdasarkanIdPengguna($idPengguna);

        if (!$profil) {
            $daftar = $this->modelSiswa->ambilSemua(null, null, 1);
            return $daftar[0] ?? [
                'id' => 1,
                'nis' => '20261001',
                'nama_lengkap' => 'Siswa SMKN 9',
                'nama_kelas' => '10 DKV 1',
                'id_kelas_aktif' => 1,
                'kode_jurusan' => 'DKV',
                'nama_jurusan' => 'Desain Komunikasi Visual'
            ];
        }

        return $profil;
    }

    public function dashboard(): void {
        $profil = $this->getProfilSiswa();
        $idSiswa = (int)$profil['id'];
        $idKelas = (int)($profil['id_kelas_aktif'] ?? 1);
        $nilaiFinal = $this->modelNilai->ambilNilaiFinalSiswa($idSiswa);
        $jadwalKelas = $this->modelJadwal->ambilBerdasarkanKelas($idKelas);
        $eventTerbaru = $this->modelEvent->ambilEventPublik();
        $kehadiran = $this->modelNilai->ambilKehadiranSiswa($idSiswa);

        $data = [
            'judulHalaman'  => 'Dashboard Siswa - SMKN 9 Kabupaten Tangerang',
            'user'          => Session::user(),
            'siswa'         => $profil,
            'nilaiFinal'    => array_slice($nilaiFinal, 0, 5),
            'jadwalKelas'   => array_slice($jadwalKelas, 0, 5),
            'eventTerbaru'  => array_slice($eventTerbaru, 0, 3),
            'kehadiran'     => $kehadiran,
        ];

        $this->render('siswa/dashboard', $data);
    }

    /**
     * Halaman Jadwal Belajar Saya
     */
    public function jadwalBelajar(): void {
        $profil = $this->getProfilSiswa();
        $idKelas = (int)($profil['id_kelas_aktif'] ?? 1);

        $jadwalKelas = $this->modelJadwal->ambilBerdasarkanKelas($idKelas);

        $data = [
            'judulHalaman' => 'Jadwal Belajar Saya',
            'user'         => Session::user(),
            'siswa'        => $profil,
            'jadwalKelas'  => $jadwalKelas
        ];

        $this->render('siswa/jadwal', $data);
    }

    /**
     * Halaman Transkrip Rapor Nilai Siswa (HANYA Nilai Final)
     */
    public function nilaiRapor(): void {
        $profil = $this->getProfilSiswa();
        $idSiswa = (int)$profil['id'];

        $daftarTahunAjaran = $this->modelSiswa->ambilRiwayatTahunAjaran($idSiswa);
        $idTahunDiminta = filter_var($_GET['tahun'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $idTahunAktif = null;
        foreach ($daftarTahunAjaran as $tahun) {
            if ((int)$tahun['apakah_aktif'] === 1) {
                $idTahunAktif = (int)$tahun['id'];
                break;
            }
        }

        $idTahun = null;
        foreach ($daftarTahunAjaran as $tahun) {
            if ($idTahunDiminta !== false && (int)$tahun['id'] === $idTahunDiminta) {
                $idTahun = (int)$tahun['id'];
                break;
            }
        }
        $idTahun ??= $idTahunAktif ?? (isset($daftarTahunAjaran[0]) ? (int)$daftarTahunAjaran[0]['id'] : null);

        $daftarSemester = $idTahun === null
            ? []
            : $this->modelNilai->ambilSemesterRiwayatSiswa($idSiswa, $idTahun);
        $idSemesterAktif = $this->modelNilai->ambilIdSemesterAktif();
        $idSemesterDiminta = filter_var($_GET['semester'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $semesterTerpilih = null;
        foreach ($daftarSemester as $semester) {
            if (($idSemesterDiminta !== false && (int)$semester['id'] === $idSemesterDiminta)
                || ($idSemesterDiminta === false && $idSemesterAktif !== null
                    && (int)$semester['id'] === $idSemesterAktif)
            ) {
                $semesterTerpilih = $semester;
                break;
            }
        }
        $semesterTerpilih ??= $daftarSemester ? $daftarSemester[count($daftarSemester) - 1] : null;
        $idSemester = $semesterTerpilih === null ? null : (int)$semesterTerpilih['id'];

        $nilaiFinal = $idSemester === null
            ? []
            : $this->modelNilai->ambilNilaiFinalSiswa($idSiswa, $idSemester);
        $kehadiran = $idSemester === null
            ? ['sakit' => null, 'izin' => null, 'alfa' => null, 'catatan' => null]
            : $this->modelNilai->ambilKehadiranSiswa($idSiswa, $idSemester);
        $tahunTerpilih = null;
        foreach ($daftarTahunAjaran as $tahun) {
            if ((int)$tahun['id'] === $idTahun) {
                $tahunTerpilih = $tahun;
                break;
            }
        }
        $kelasTahunTerpilih = $idTahun === null
            ? null
            : $this->modelSiswa->ambilKelasPadaTahun($idSiswa, $idTahun);

        $data = [
            'judulHalaman'   => 'Transkrip Nilai & e-Rapor Siswa',
            'user'           => Session::user(),
            'siswa'          => $profil,
            'nilaiFinal'     => $nilaiFinal,
            'daftarTahunAjaran' => $daftarTahunAjaran,
            'tahunTerpilih'  => $tahunTerpilih,
            'daftarSemester' => $daftarSemester,
            'idSemester'     => $idSemester,
            'semesterTerpilih' => $semesterTerpilih,
            'kelasTahunTerpilih' => $kelasTahunTerpilih,
            'kehadiran'      => $kehadiran
        ];

        $this->render('siswa/nilai', $data);
    }

    /**
     * Halaman Event & Mading Sekolah
     */
    public function eventSekolah(): void {
        $profil = $this->getProfilSiswa();
        $idKategori = !empty($_GET['kategori']) ? (int)$_GET['kategori'] : null;

        $daftarEvent = $this->modelEvent->ambilEventPublik($idKategori);
        $daftarKategori = $this->modelEvent->ambilKategori();

        $data = [
            'judulHalaman'   => 'Event & Mading Sekolah SMKN 9 Kabupaten Tangerang',
            'user'           => Session::user(),
            'siswa'          => $profil,
            'daftarEvent'    => $daftarEvent,
            'daftarKategori' => $daftarKategori,
            'filterKategori' => $idKategori
        ];

        $this->render('siswa/event_list', $data);
    }
}
