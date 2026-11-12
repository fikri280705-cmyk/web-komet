<?php
/**
 * Controller Dasbor Wali Kelas
 * Terisolasi ketat pada Kelas Binaan Aktif
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Jadwal;
use App\Models\Nilai;
use App\Models\Presensi;
use App\Helpers\Session;
use App\Helpers\Audit;

class WaliKelasController extends Controller {
    private Kelas $modelKelas;
    private Siswa $modelSiswa;
    private Jadwal $modelJadwal;
    private Nilai $modelNilai;
    private Presensi $modelPresensi;

    public function __construct() {
        $this->modelKelas = new Kelas();
        $this->modelSiswa = new Siswa();
        $this->modelJadwal = new Jadwal();
        $this->modelNilai = new Nilai();
        $this->modelPresensi = new Presensi();
    }

    private function getKelasBinaan(): array {
        $idPengguna = Session::get('pengguna_id');
        $kelasBinaan = $this->modelKelas->cariKelasWaliAktif($idPengguna);

        if (!$kelasBinaan) {
            // Fallback default jika diakses mode demo
            $daftar = $this->modelKelas->ambilSemua();
            return $daftar[0] ?? ['id' => 1, 'nama_kelas' => '10 DKV 1', 'tingkat' => 10, 'nama_jurusan' => 'Desain Komunikasi Visual', 'kode_jurusan' => 'DKV'];
        }

        return $kelasBinaan;
    }

    public function dashboard(): void {
        $kelasBinaan = $this->getKelasBinaan();
        $idKelas = (int)$kelasBinaan['id'];

        $daftarSiswa = $this->modelSiswa->ambilBerdasarkanKelas($idKelas);
        $jadwalKelas = $this->modelJadwal->ambilBerdasarkanKelas($idKelas);
        $rekapAbsensi = $this->modelNilai->ambilKehadiranKelas($idKelas);

        $data = [
            'judulHalaman'  => "Dashboard Wali Kelas - {$kelasBinaan['nama_kelas']}",
            'user'          => Session::user(),
            'kelas'         => $kelasBinaan,
            'daftarSiswa'   => $daftarSiswa,
            'totalSiswa'    => count($daftarSiswa),
            'jadwalKelas'   => array_slice($jadwalKelas, 0, 5),
            'previewSiswa'  => array_slice($daftarSiswa, 0, 5),
            'previewAbsen'  => array_slice($rekapAbsensi, 0, 5),
        ];

        $this->render('wali_kelas/dashboard', $data);
    }

    /**
     * Halaman Siswa Kelas Binaan
     */
    public function siswaBinaan(): void {
        $kelasBinaan = $this->getKelasBinaan();
        $idKelas = (int)$kelasBinaan['id'];
        $daftarSiswa = $this->modelSiswa->ambilBerdasarkanKelas($idKelas);
        $presensiHariIni = $this->modelPresensi->ambilMapStatusKelasHariIni($idKelas);

        $data = [
            'judulHalaman' => "Siswa Kelas Binaan {$kelasBinaan['nama_kelas']}",
            'user'         => Session::user(),
            'kelas'        => $kelasBinaan,
            'daftarSiswa'  => $daftarSiswa,
            'presensiHariIni' => $presensiHariIni,
            'tanggalPresensi' => date('Y-m-d'),
        ];

        $this->render('wali_kelas/siswa_list', $data);
    }

    /**
     * Simpan batch kehadiran harian siswa binaan + WA ke orang tua
     */
    public function simpanPresensiKelas(): void {
        $kelasBinaan = $this->getKelasBinaan();
        $idKelas = (int)$kelasBinaan['id'];
        $daftarSiswa = $this->modelSiswa->ambilBerdasarkanKelas($idKelas);
        $presensiLama = $this->modelPresensi->ambilMapStatusKelasHariIni($idKelas);
        $inputPresensi = $_POST['presensi'] ?? [];

        if (!is_array($inputPresensi) || $inputPresensi === []) {
            Session::setFlash('error', 'Tidak ada data kehadiran yang dikirim.');
            $this->redirect('/wali-kelas/siswa');
            return;
        }

        $mapSiswa = [];
        foreach ($daftarSiswa as $row) {
            $mapSiswa[(int)$row['id']] = $row;
        }

        $tersimpan = 0;
        $waOk = 0;
        $waGagal = 0;
        $contohGagalWa = '';
        $idPengguna = (int)Session::get('pengguna_id');
        $kirimUlang = isset($_POST['kirim_ulang_wa']);

        foreach ($inputPresensi as $idSiswaInput => $nilai) {
            $idSiswa = filter_var($idSiswaInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($idSiswa === false || !isset($mapSiswa[$idSiswa])) {
                continue;
            }

            // Terima hadir/izin/sakit/tidak_hadir (atau 1/0 kompatibilitas lama)
            if (is_array($nilai)) {
                $nilai = end($nilai);
            }
            $nilaiNorm = strtolower(trim((string)$nilai));
            $statusBaru = match ($nilaiNorm) {
                '1', 'hadir', 'on', 'true' => 'hadir',
                'izin' => 'izin',
                'sakit' => 'sakit',
                default => 'tidak_hadir',
            };

            $statusLama = $presensiLama[$idSiswa] ?? null;
            $berubah = $statusLama === null || $statusLama !== $statusBaru;
            // Default: kirim WA untuk SEMUA status (hadir/izin/sakit/tidak_hadir)
            // saat berubah. Centang "kirim ulang" untuk kirim lagi meski tidak berubah.
            $lewatiWa = isset($_POST['lewati_wa']);
            $perluWa = !$lewatiWa && ($berubah || $kirimUlang);
            $siswa = $mapSiswa[$idSiswa];

            if ($perluWa) {
                $hasil = $this->modelPresensi->setStatusDanKirimWa(
                    $idSiswa,
                    $statusBaru,
                    $idPengguna,
                    (string)$siswa['nama_lengkap'],
                    (string)$kelasBinaan['nama_kelas'],
                    $siswa['telepon_orang_tua'] ?? null,
                    $siswa['nomor_telepon'] ?? null
                );
                $tersimpan++;
                if ($hasil['wa_ok']) {
                    $waOk++;
                } else {
                    $waGagal++;
                    if (($contohGagalWa ?? '') === '') {
                        $contohGagalWa = (string)($hasil['wa_message'] ?? $hasil['wa_status'] ?? 'gagal');
                    }
                }

                Audit::catat(
                    'PRESENSI_HARIAN',
                    'presensi_harian',
                    $idSiswa,
                    $statusLama ? ['status' => $statusLama] : null,
                    [
                        'status' => $statusBaru,
                        'tanggal' => date('Y-m-d'),
                        'wa_status' => $hasil['wa_status'],
                    ]
                );

                // Jeda antar nomor agar Fonnte tidak membuang pesan massal
                usleep(450000);
            } else {
                $this->modelPresensi->setStatusSaja($idSiswa, $statusBaru, $idPengguna);
                if ($berubah) {
                    $tersimpan++;
                }
            }
        }

        if ($tersimpan === 0 && $waOk === 0 && $waGagal === 0) {
            Session::setFlash('success', 'Tidak ada perubahan kehadiran. Centang "Kirim ulang WhatsApp" jika ingin mengirim lagi.');
        } else {
            $pesan = "Presensi diproses. WhatsApp: {$waOk} terkirim, {$waGagal} gagal (Hadir/Izin/Sakit/Tidak Hadir).";
            if ($waGagal > 0 && ($contohGagalWa ?? '') !== '') {
                $pesan .= ' Penyebab: ' . mb_substr($contohGagalWa, 0, 160);
            }
            Session::setFlash($waOk > 0 ? 'success' : 'error', $pesan);
        }

        $this->redirect('/wali-kelas/siswa');
    }

    /**
     * Halaman Jadwal Pelajaran Kelas
     */
    public function jadwalPelajaran(): void {
        $kelasBinaan = $this->getKelasBinaan();
        $idKelas = (int)$kelasBinaan['id'];
        $jadwalKelas = $this->modelJadwal->ambilBerdasarkanKelas($idKelas);

        $data = [
            'judulHalaman' => "Jadwal Pelajaran Kelas {$kelasBinaan['nama_kelas']}",
            'user'         => Session::user(),
            'kelas'        => $kelasBinaan,
            'jadwalKelas'  => $jadwalKelas
        ];

        $this->render('wali_kelas/jadwal_list', $data);
    }

    /**
     * Halaman Input Rapor Nilai
     */
    public function inputNilai(): void {
        $kelasBinaan = $this->getKelasBinaan();
        $idKelas = (int)$kelasBinaan['id'];

        $daftarMapel = $this->modelNilai->ambilMapelKelas($idKelas);
        $idPenugasanDiminta = isset($_GET['penugasan'])
            ? filter_var($_GET['penugasan'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : null;
        $pemetaanMapel = [];
        foreach ($daftarMapel as $mapel) {
            $pemetaanMapel[(int)$mapel['id_penugasan_guru']] = $mapel;
        }

        $idPenugasan = $idPenugasanDiminta !== false && isset($pemetaanMapel[$idPenugasanDiminta])
            ? $idPenugasanDiminta
            : (int)($daftarMapel[0]['id_penugasan_guru'] ?? 0);
        if ($idPenugasanDiminta !== null && ($idPenugasanDiminta === false || !isset($pemetaanMapel[$idPenugasanDiminta]))) {
            Session::setFlash('error', 'Mata pelajaran tidak ditemukan untuk kelas binaan dan semester aktif.');
        }

        $daftarNilai = [];
        if ($idPenugasan) {
            $daftarNilai = $this->modelNilai->ambilNilaiRombel($idKelas, $idPenugasan);
        }

        $data = [
            'judulHalaman' => "Input Rapor Nilai - Kelas {$kelasBinaan['nama_kelas']}",
            'user'         => Session::user(),
            'kelas'        => $kelasBinaan,
            'daftarMapel'  => $daftarMapel,
            'idPenugasan'  => $idPenugasan,
            'daftarNilai'  => $daftarNilai
        ];

        $this->render('wali_kelas/input_nilai', $data);
    }

    /**
     * Simpan Rapor Nilai Siswa
     */
    public function simpanNilai(): void {
        $idPenugasan = filter_var($_POST['id_penugasan'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $nilaiData = $_POST['nilai'] ?? [];
        $idSemester = $this->modelNilai->ambilIdSemesterAktif();
        $kelasBinaan = $this->getKelasBinaan();
        $idKelas = (int)$kelasBinaan['id'];
        $redirectPenugasan = $idPenugasan === false ? 0 : $idPenugasan;

        if ($idPenugasan === false || $idSemester === null || !is_array($nilaiData)) {
            Session::setFlash('error', 'Data nilai tidak valid atau semester aktif belum tersedia.');
            $this->redirect("/wali-kelas/nilai?penugasan={$redirectPenugasan}");
            return;
        }

        $daftarMapel = $this->modelNilai->ambilMapelKelas($idKelas, $idSemester);
        $penugasanValid = false;
        foreach ($daftarMapel as $mapel) {
            if ((int)$mapel['id_penugasan_guru'] === $idPenugasan) {
                $penugasanValid = true;
                break;
            }
        }
        if (!$penugasanValid) {
            Session::setFlash('error', 'Mata pelajaran tidak terdaftar pada kelas binaan di semester aktif.');
            $this->redirect('/wali-kelas/nilai');
            return;
        }

        $daftarNilai = $this->modelNilai->ambilNilaiRombel($idKelas, $idPenugasan, $idSemester);
        $siswaKelas = [];
        foreach ($daftarNilai as $baris) {
            $siswaKelas[(int)$baris['id_siswa']] = $baris;
        }

        $nilaiAwal = [];
        foreach ($nilaiData as $idSiswaInput => $item) {
            $idSiswa = filter_var($idSiswaInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($idSiswa === false || !isset($siswaKelas[$idSiswa]) || !is_array($item)) {
                Session::setFlash('error', 'Data siswa tidak valid atau bukan bagian dari kelas binaan.');
                $this->redirect("/wali-kelas/nilai?penugasan={$idPenugasan}");
                return;
            }

            if ($siswaKelas[$idSiswa]['id_nilai'] !== null) {
                continue;
            }

            if ($this->nilaiFormKosong($item)) {
                continue;
            }
            $nilai = $this->validasiNilai($item);
            if ($nilai === null) {
                Session::setFlash('error', 'Isi seluruh komponen nilai dengan angka 0 sampai 100.');
                $this->redirect("/wali-kelas/nilai?penugasan={$idPenugasan}");
                return;
            }
            $nilaiAwal[$idSiswa] = $nilai;
        }

        if ($nilaiAwal === []) {
            Session::setFlash('error', 'Tidak ada nilai baru yang disimpan; nilai yang sudah ada tetap terkunci.');
            $this->redirect("/wali-kelas/nilai?penugasan={$idPenugasan}");
            return;
        }

        $jumlahTersimpan = $this->modelNilai->simpanNilaiAwalKelas(
            $idKelas,
            $idPenugasan,
            $idSemester,
            $nilaiAwal,
            (int)Session::get('pengguna_id')
        );
        if ($jumlahTersimpan > 0) {
            Session::setFlash('success', "{$jumlahTersimpan} nilai awal berhasil disimpan dan dikalkulasi. Nilai yang sudah ada tidak diubah.");
        } else {
            Session::setFlash('error', 'Tidak ada nilai baru yang disimpan; nilai yang sudah ada tetap terkunci.');
        }

        $this->redirect("/wali-kelas/nilai?penugasan={$idPenugasan}");
    }

    public function perbaruiNilaiSiswa(): void {
        $idSiswa = filter_var($_POST['id_siswa'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $idPenugasan = filter_var($_POST['id_penugasan'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $redirectPenugasan = $idPenugasan === false ? 0 : $idPenugasan;
        if ($idSiswa === false || $idPenugasan === false) {
            Session::setFlash('error', 'Siswa atau mata pelajaran yang akan diperbarui tidak valid.');
            $this->redirect('/wali-kelas/nilai');
            return;
        }

        $nilai = $this->validasiNilai($_POST);
        if ($nilai === null) {
            Session::setFlash('error', 'Isi seluruh komponen nilai dengan angka 0 sampai 100.');
            $this->redirect("/wali-kelas/nilai?penugasan={$redirectPenugasan}");
            return;
        }

        $idSemester = $this->modelNilai->ambilIdSemesterAktif();
        if ($idSemester === null) {
            Session::setFlash('error', 'Nilai tidak dapat diperbarui karena semester aktif belum tersedia.');
            $this->redirect("/wali-kelas/nilai?penugasan={$idPenugasan}");
            return;
        }

        $kelasBinaan = $this->getKelasBinaan();
        $idKelas = (int)$kelasBinaan['id'];
        $penugasanValid = false;
        foreach ($this->modelNilai->ambilMapelKelas($idKelas, $idSemester) as $mapel) {
            if ((int)$mapel['id_penugasan_guru'] === $idPenugasan) {
                $penugasanValid = true;
                break;
            }
        }
        if (!$penugasanValid) {
            Session::setFlash('error', 'Mata pelajaran tidak terdaftar pada kelas binaan di semester aktif.');
            $this->redirect('/wali-kelas/nilai');
            return;
        }

        $berhasil = $this->modelNilai->perbaruiNilaiSiswa(
            $idSiswa,
            $idKelas,
            $idPenugasan,
            $idSemester,
            $nilai['tugas'],
            $nilai['uts'],
            $nilai['uas'],
            $nilai['praktik'],
            (int)Session::get('pengguna_id')
        );

        if ($berhasil) {
            Session::setFlash('success', 'Nilai berhasil diperbarui. Perubahan hanya diterapkan pada siswa yang dipilih.');
        } else {
            Session::setFlash('error', 'Nilai siswa tidak ditemukan pada mata pelajaran dan semester aktif.');
        }
        $this->redirect("/wali-kelas/nilai?penugasan={$idPenugasan}");
    }

    private function nilaiFormKosong(array $nilai): bool {
        foreach (['tugas', 'uts', 'uas', 'praktik'] as $komponen) {
            $input = $nilai[$komponen] ?? null;
            if ($input !== null && (!is_string($input) || trim($input) !== '')) {
                return false;
            }
        }

        return true;
    }

    private function validasiNilai(array $data): ?array {
        $nilai = [];
        foreach (['tugas', 'uts', 'uas', 'praktik'] as $komponen) {
            $input = $data[$komponen] ?? null;
            if (!is_string($input) && !is_int($input) && !is_float($input)) {
                return null;
            }
            $input = trim((string)$input);
            if ($input === '' || !is_numeric($input)) {
                return null;
            }

            $angka = (float)$input;
            if (!is_finite($angka) || $angka < 0 || $angka > 100) {
                return null;
            }
            $nilai[$komponen] = $angka;
        }

        return $nilai;
    }

    /**
     * Halaman Rekap Absensi Siswa
     */
    public function rekapAbsensi(): void {
        $kelasBinaan = $this->getKelasBinaan();
        $idKelas = (int)$kelasBinaan['id'];

        $rekapAbsensi = $this->modelNilai->ambilKehadiranKelas($idKelas);

        $data = [
            'judulHalaman' => "Rekap Absensi Siswa - Kelas {$kelasBinaan['nama_kelas']}",
            'user'         => Session::user(),
            'kelas'        => $kelasBinaan,
            'rekapAbsensi' => $rekapAbsensi
        ];

        $this->render('wali_kelas/rekap_absensi', $data);
    }

    /**
     * Simpan Rekap Absensi Siswa
     */
    public function simpanAbsensi(): void {
        $absensiData = $_POST['absen'] ?? [];
        $idSemester = $this->modelNilai->ambilIdSemesterAktif();

        if ($idSemester === null) {
            Session::setFlash('error', 'Rekap absensi tidak dapat disimpan karena semester aktif belum tersedia.');
            $this->redirect('/wali-kelas/absensi');
            return;
        }

        $kelasBinaan = $this->getKelasBinaan();
        $idKelas = (int)$kelasBinaan['id'];
        $rekapKelas = $this->modelNilai->ambilKehadiranKelas($idKelas, $idSemester);
        $siswaKelas = [];
        foreach ($rekapKelas as $rekap) {
            $siswaKelas[(int)$rekap['id_siswa']] = $rekap;
        }

        if (!is_array($absensiData) || $absensiData === []) {
            Session::setFlash('error', 'Tidak ada data absensi awal yang dikirim.');
            $this->redirect('/wali-kelas/absensi');
            return;
        }

        $dataAwal = [];
        foreach ($absensiData as $idSiswaInput => $row) {
            $idSiswa = filter_var($idSiswaInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($idSiswa === false || !isset($siswaKelas[$idSiswa]) || !is_array($row)) {
                Session::setFlash('error', 'Data siswa tidak valid atau bukan bagian dari kelas binaan Anda.');
                $this->redirect('/wali-kelas/absensi');
                return;
            }

            if ($siswaKelas[$idSiswa]['id_rekap'] !== null) {
                continue;
            }

            $jumlah = $this->validasiJumlahAbsensi($row);
            if ($jumlah === null) {
                Session::setFlash('error', 'Isi angka Sakit, Izin, dan Alfa dengan bilangan bulat 0 sampai 100.');
                $this->redirect('/wali-kelas/absensi');
                return;
            }

            $catatan = $row['catatan'] ?? '';
            if (!is_string($catatan) || mb_strlen($catatan) > 255) {
                Session::setFlash('error', 'Catatan absensi maksimal 255 karakter.');
                $this->redirect('/wali-kelas/absensi');
                return;
            }
            $jumlah['catatan'] = trim($catatan);
            $dataAwal[$idSiswa] = $jumlah;
        }

        if ($dataAwal === []) {
            Session::setFlash('error', 'Tidak ada rekap baru yang disimpan; data yang sudah ada tidak diubah.');
            $this->redirect('/wali-kelas/absensi');
            return;
        }

        $jumlahTersimpan = $this->modelNilai->simpanKehadiranAwalKelas($idKelas, $idSemester, $dataAwal);
        if ($jumlahTersimpan > 0) {
            Session::setFlash('success', "{$jumlahTersimpan} rekap absensi awal berhasil disimpan. Data yang sudah ada tetap terkunci.");
        } else {
            Session::setFlash('error', 'Tidak ada rekap baru yang disimpan; data yang sudah ada tidak diubah.');
        }

        $this->redirect('/wali-kelas/absensi');
    }

    public function perbaruiAbsensiSiswa(): void {
        $idSiswa = filter_var($_POST['id_siswa'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($idSiswa === false) {
            Session::setFlash('error', 'Siswa yang akan diperbarui tidak valid.');
            $this->redirect('/wali-kelas/absensi');
            return;
        }

        $jumlah = $this->validasiJumlahAbsensi($_POST);
        if ($jumlah === null) {
            Session::setFlash('error', 'Isi angka Sakit, Izin, dan Alfa dengan bilangan bulat 0 sampai 100.');
            $this->redirect('/wali-kelas/absensi');
            return;
        }

        $catatan = $_POST['catatan'] ?? '';
        if (!is_string($catatan) || mb_strlen($catatan) > 255) {
            Session::setFlash('error', 'Catatan absensi maksimal 255 karakter.');
            $this->redirect('/wali-kelas/absensi');
            return;
        }

        $idSemester = $this->modelNilai->ambilIdSemesterAktif();
        if ($idSemester === null) {
            Session::setFlash('error', 'Rekap absensi tidak dapat diperbarui karena semester aktif belum tersedia.');
            $this->redirect('/wali-kelas/absensi');
            return;
        }

        $kelasBinaan = $this->getKelasBinaan();
        $berhasil = $this->modelNilai->perbaruiKehadiranSiswa(
            $idSiswa,
            (int)$kelasBinaan['id'],
            $idSemester,
            $jumlah['sakit'],
            $jumlah['izin'],
            $jumlah['alfa'],
            trim($catatan)
        );

        if ($berhasil) {
            Session::setFlash('success', 'Rekap absensi siswa berhasil diperbarui. Perubahan hanya diterapkan pada siswa yang dipilih.');
        } else {
            Session::setFlash('error', 'Rekap absensi siswa tidak ditemukan pada kelas binaan dan semester aktif.');
        }
        $this->redirect('/wali-kelas/absensi');
    }

    private function validasiJumlahAbsensi(array $data): ?array {
        $jumlah = [];
        foreach (['sakit', 'izin', 'alfa'] as $jenis) {
            $nilai = filter_var(
                $data[$jenis] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 0, 'max_range' => 100]]
            );
            if ($nilai === false) {
                return null;
            }
            $jumlah[$jenis] = $nilai;
        }

        return $jumlah;
    }
}
