<?php
/**
 * Pengaturan tahun ajaran dan persiapan kenaikan kelas untuk Kesiswaan.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Jadwal;
use App\Models\TahunAjaran;
use App\Services\TahunAjaranSpreadsheet;
use InvalidArgumentException;

class TahunAjaranController extends Controller {
    private TahunAjaran $modelTahunAjaran;
    private Jadwal $modelJadwal;

    public function __construct() {
        $this->modelTahunAjaran = new TahunAjaran();
        $this->modelJadwal = new Jadwal();
    }

    public function index(): void {
        $daftarTahun = $this->modelTahunAjaran->daftarTahunAjaran();
        $periodeAktif = $this->modelTahunAjaran->ambilPeriodeAktif();
        $idTahunDiminta = $this->angka($_GET['tahun'] ?? null);
        $tahunTerpilih = null;
        foreach ($daftarTahun as $tahun) {
            if ($idTahunDiminta !== null && (int)$tahun['id'] === $idTahunDiminta) {
                $tahunTerpilih = $tahun;
                break;
            }
        }
        if ($tahunTerpilih === null && $idTahunDiminta === null && $periodeAktif
            && $periodeAktif['jenis_semester'] === 'genap'
        ) {
            foreach ($daftarTahun as $tahun) {
                if ((int)$tahun['apakah_aktif'] === 0
                    && $tahun['tanggal_mulai'] > $periodeAktif['tahun_selesai']
                ) {
                    $tahunTerpilih = $tahun;
                    break;
                }
            }
        }
        if ($tahunTerpilih === null && $periodeAktif) {
            foreach ($daftarTahun as $tahun) {
                if ((int)$tahun['id'] === (int)$periodeAktif['id_tahun_ajaran']) {
                    $tahunTerpilih = $tahun;
                    break;
                }
            }
        }
        $tahunTerpilih ??= $daftarTahun[0] ?? null;

        $daftarSemester = $tahunTerpilih
            ? $this->modelTahunAjaran->daftarSemester((int)$tahunTerpilih['id'])
            : [];
        $idSemesterDiminta = $this->angka($_GET['semester'] ?? null);
        $semesterTerpilih = null;
        foreach ($daftarSemester as $semester) {
            if ($idSemesterDiminta !== null && (int)$semester['id'] === $idSemesterDiminta) {
                $semesterTerpilih = $semester;
                break;
            }
        }
        if ($semesterTerpilih === null && $daftarSemester) {
            if ($periodeAktif && (int)$tahunTerpilih['id'] === (int)$periodeAktif['id_tahun_ajaran']) {
                foreach ($daftarSemester as $semester) {
                    if ($semester['jenis_semester'] === 'genap') {
                        $semesterTerpilih = $semester;
                        break;
                    }
                }
            }
            $semesterTerpilih ??= $daftarSemester[0];
        }

        $daftarKelas = $this->modelTahunAjaran->daftarKelas();
        $idKelasDiminta = $this->angka($_GET['kelas'] ?? null);
        $kelasTerpilih = null;
        foreach ($daftarKelas as $kelas) {
            if ($idKelasDiminta !== null && (int)$kelas['id'] === $idKelasDiminta) {
                $kelasTerpilih = $kelas;
                break;
            }
        }
        $kelasTerpilih ??= $daftarKelas[0] ?? null;

        $templateKurikulum = [];
        $daftarMapel = [];
        $setupKelas = [];
        $jadwalPerMapel = [];
        $kesiapanSemester = null;
        $semesterDapatDisiapkan = false;
        if ($tahunTerpilih && $semesterTerpilih) {
            $idTahun = (int)$tahunTerpilih['id'];
            $idSemester = (int)$semesterTerpilih['id'];
            $kesiapanSemester = $this->modelTahunAjaran->evaluasiKesiapanSemester($idSemester);
            $semesterDapatDisiapkan = $this->modelTahunAjaran->semesterDapatDisiapkan($idTahun, $idSemester);
            if ($kelasTerpilih) {
                $templateKurikulum = $this->modelTahunAjaran->ambilTemplateKurikulum(
                    $idTahun,
                    $semesterTerpilih['jenis_semester'],
                    (int)$kelasTerpilih['id_jurusan'],
                    (int)$kelasTerpilih['tingkat']
                );
                $daftarMapel = $this->modelTahunAjaran->daftarMapel((int)$kelasTerpilih['id_jurusan']);
                $setupKelas = $this->modelTahunAjaran->ambilSetupKelasSemester(
                    $idTahun,
                    $idSemester,
                    (int)$kelasTerpilih['id']
                );
                foreach ($setupKelas as $setup) {
                    $idPenugasan = (int)($setup['id_penugasan'] ?? 0);
                    $jadwalPerMapel[(int)$setup['id_mapel']] = $idPenugasan > 0
                        ? $this->modelTahunAjaran->ambilJadwalPenugasan($idPenugasan)
                        : [];
                }
            }
        }

        $kesiapanTahunBaru = [];
        $pratinjauKenaikan = null;
        $waliDapatDisiapkan = $tahunTerpilih !== null
            && (int)$tahunTerpilih['apakah_aktif'] === 0
            && $periodeAktif !== null
            && $tahunTerpilih['tanggal_mulai'] > $periodeAktif['tahun_selesai'];
        if ($tahunTerpilih && $periodeAktif && $periodeAktif['jenis_semester'] === 'genap'
            && (int)$tahunTerpilih['id'] !== (int)$periodeAktif['id_tahun_ajaran']
        ) {
            $pratinjauKenaikan = $this->modelTahunAjaran->pratinjauKenaikan((int)$tahunTerpilih['id']);
            foreach ($daftarSemester as $semester) {
                $kesiapanTahunBaru[] = $this->modelTahunAjaran->evaluasiKesiapanSemester((int)$semester['id']);
            }
        }

        $this->render('kesiswaan/tahun_ajaran', [
            'judulHalaman' => 'Tahun Ajaran & Kenaikan Kelas',
            'user' => Session::user(),
            'daftarTahun' => $daftarTahun,
            'tahunTerpilih' => $tahunTerpilih,
            'daftarSemester' => $daftarSemester,
            'semesterTerpilih' => $semesterTerpilih,
            'daftarKelas' => $daftarKelas,
            'kelasTerpilih' => $kelasTerpilih,
            'daftarJurusan' => $this->modelTahunAjaran->daftarJurusan(),
            'daftarGuru' => $this->modelTahunAjaran->ambilDaftarGuru(),
            'daftarWali' => $tahunTerpilih
                ? $this->modelTahunAjaran->ambilDaftarWali((int)$tahunTerpilih['id'])
                : [],
            'templateKurikulum' => $templateKurikulum,
            'daftarMapel' => $daftarMapel,
            'setupKelas' => $setupKelas,
            'jadwalPerMapel' => $jadwalPerMapel,
            'periodeAktif' => $periodeAktif,
            'kesiapanSemester' => $kesiapanSemester,
            'semesterDapatDisiapkan' => $semesterDapatDisiapkan,
            'waliDapatDisiapkan' => $waliDapatDisiapkan,
            'kesiapanTahunBaru' => $kesiapanTahunBaru,
            'pratinjauKenaikan' => $pratinjauKenaikan
        ]);
    }

    public function buatDraft(): void {
        try {
            $idTahun = $this->modelTahunAjaran->buatDraft($_POST);
            Session::setFlash('success', 'Draft tahun ajaran berhasil dibuat. Lengkapi wali kelas, kurikulum, pengajar, dan jadwal sebelum aktivasi.');
            $this->redirect('/kesiswaan/tahun-ajaran?tahun=' . $idTahun);
        } catch (InvalidArgumentException $e) {
            Session::setFlash('error', $e->getMessage());
            $this->redirect('/kesiswaan/tahun-ajaran');
        }
    }

    public function unduhTemplateImpor(): void {
        $path = tempnam(sys_get_temp_dir(), 'template-tahun-ajaran-');
        if ($path === false) {
            error_log('Template impor tahun ajaran gagal: berkas sementara tidak dapat dibuat.');
            Session::setFlash('error', 'Template XLSX tidak dapat dibuat saat ini.');
            $this->redirect('/kesiswaan/tahun-ajaran');
        }
        if (!unlink($path)) {
            error_log('Template impor tahun ajaran gagal: berkas sementara tidak dapat dikosongkan.');
            Session::setFlash('error', 'Template XLSX tidak dapat dibuat saat ini.');
            $this->redirect('/kesiswaan/tahun-ajaran');
        }

        try {
            (new TahunAjaranSpreadsheet())->buatTemplate($path);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="template-impor-tahun-ajaran.xlsx"');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            unlink($path);
            exit;
        } catch (\Throwable $e) {
            if (is_file($path)) {
                unlink($path);
            }
            error_log('Template impor tahun ajaran gagal: ' . $e->getMessage());
            Session::setFlash('error', 'Template XLSX tidak dapat dibuat saat ini.');
            $this->redirect('/kesiswaan/tahun-ajaran');
        }
    }

    public function imporPersiapan(): void {
        $idTahun = $this->angka($_POST['id_tahun'] ?? null);
        $file = $_FILES['file_impor'] ?? null;
        if ($idTahun === null || !is_array($file)) {
            Session::setFlash('error', 'Pilih tahun ajaran dan berkas XLSX atau CSV yang akan diimpor.');
            $this->kembali($idTahun, null, null);
            return;
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || !is_string($file['tmp_name'] ?? null)
            || !is_uploaded_file($file['tmp_name'])
        ) {
            Session::setFlash('error', 'Unggah berkas gagal. Pilih berkas XLSX atau CSV yang valid dan coba lagi.');
            $this->kembali($idTahun, null, null);
            return;
        }
        if ((int)($file['size'] ?? 0) <= 0 || (int)$file['size'] > 5 * 1024 * 1024) {
            Session::setFlash('error', 'Ukuran berkas harus lebih dari 0 dan maksimal 5 MB.');
            $this->kembali($idTahun, null, null);
            return;
        }
        $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($extension, ['xlsx', 'csv'], true)) {
            Session::setFlash('error', 'Format berkas tidak didukung. Gunakan XLSX atau CSV.');
            $this->kembali($idTahun, null, null);
            return;
        }

        try {
            $rows = (new TahunAjaranSpreadsheet())->baca($file['tmp_name'], $extension);
            $result = $this->modelTahunAjaran->imporPersiapan($idTahun, $rows);
            if ($result['errors'] !== []) {
                $messages = array_slice($result['errors'], 0, 10);
                $suffix = count($result['errors']) > 10 ? ' | dan masalah lainnya.' : '';
                Session::setFlash('error', 'Impor dibatalkan: ' . implode(' | ', $messages) . $suffix);
            } else {
                $counts = $result['counts'];
                $summary = sprintf(
                    'Impor berhasil: %d wali kelas, %d kurikulum, %d penugasan pengajar, dan %d jadwal diproses.',
                    $counts['wali_kelas'],
                    $counts['kurikulum'],
                    $counts['pengajar'],
                    $counts['jadwal']
                );
                Session::setFlash('success', $summary);
                \App\Helpers\Audit::catat(
                    'IMPOR_PERSIAPAN',
                    'Tahun Ajaran',
                    $idTahun,
                    null,
                    ['jumlah_baris' => count($rows), 'jumlah' => $counts]
                );
            }
        } catch (InvalidArgumentException $e) {
            Session::setFlash('error', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Impor persiapan tahun ajaran gagal: ' . $e->getMessage());
            Session::setFlash('error', 'Impor gagal karena kesalahan sistem. Tidak ada perubahan data yang disimpan.');
        }
        $this->kembali($idTahun, null, null);
    }

    public function simpanTemplate(): void {
        $idTahun = $this->angka($_POST['id_tahun'] ?? null);
        $idSemester = $this->angka($_POST['id_semester'] ?? null);
        $idJurusan = $this->angka($_POST['id_jurusan'] ?? null);
        $tingkat = $this->angka($_POST['tingkat'] ?? null);
        $idMapel = $this->angka($_POST['id_mapel'] ?? null);
        $jumlah = $this->angka($_POST['pertemuan_per_minggu'] ?? null);
        if (!$idTahun || !$idSemester || !$idJurusan || !$tingkat || !$idMapel || !$jumlah) {
            $this->gagalInput($idTahun, $idSemester, $_POST['id_kelas'] ?? null);
            return;
        }
        $semester = $this->semesterMilikTahun($idTahun, $idSemester);
        if (!$semester) {
            Session::setFlash('error', 'Semester tidak ditemukan untuk tahun ajaran yang dipilih.');
            $this->kembali($idTahun, $idSemester, $_POST['id_kelas'] ?? null);
            return;
        }
        $error = $this->modelTahunAjaran->simpanTemplate(
            $idTahun,
            $idJurusan,
            $tingkat,
            $semester['jenis_semester'],
            $idMapel,
            $jumlah
        );
        $this->flashHasil($error, 'Template kurikulum berhasil disimpan.');
        $this->kembali($idTahun, $idSemester, $_POST['id_kelas'] ?? null);
    }

    public function hapusTemplate(): void {
        $idTahun = $this->angka($_POST['id_tahun'] ?? null);
        $idSemester = $this->angka($_POST['id_semester'] ?? null);
        $idTemplate = $this->angka($_POST['id_template'] ?? null);
        if (!$idTahun || !$idSemester || !$idTemplate) {
            $this->gagalInput($idTahun, $idSemester, $_POST['id_kelas'] ?? null);
            return;
        }
        $this->flashHasil($this->modelTahunAjaran->hapusTemplate($idTemplate), 'Template kurikulum berhasil dihapus.');
        $this->kembali($idTahun, $idSemester, $_POST['id_kelas'] ?? null);
    }

    public function simpanWali(): void {
        $idTahun = $this->angka($_POST['id_tahun'] ?? null);
        $idKelas = $this->angka($_POST['id_kelas'] ?? null);
        $idGuru = $this->angka($_POST['id_guru'] ?? null);
        if (!$idTahun || !$idKelas || !$idGuru) {
            $this->gagalInput($idTahun, $_POST['id_semester'] ?? null, $idKelas);
            return;
        }
        $this->flashHasil(
            $this->modelTahunAjaran->simpanWali($idTahun, $idKelas, $idGuru),
            'Penugasan wali kelas berhasil disimpan.'
        );
        $this->kembali($idTahun, $_POST['id_semester'] ?? null, $idKelas);
    }

    public function simpanPenugasan(): void {
        $idTahun = $this->angka($_POST['id_tahun'] ?? null);
        $idSemester = $this->angka($_POST['id_semester'] ?? null);
        $idKelas = $this->angka($_POST['id_kelas'] ?? null);
        $idMapel = $this->angka($_POST['id_mapel'] ?? null);
        $idGuru = $this->angka($_POST['id_guru'] ?? null);
        if (!$idTahun || !$idSemester || !$idKelas || !$idMapel || !$idGuru) {
            $this->gagalInput($idTahun, $idSemester, $idKelas);
            return;
        }
        $this->flashHasil(
            $this->modelTahunAjaran->simpanPenugasan($idTahun, $idSemester, $idKelas, $idMapel, $idGuru),
            'Guru pengajar berhasil ditetapkan.'
        );
        $this->kembali($idTahun, $idSemester, $idKelas);
    }

    public function hapusPenugasan(): void {
        $idTahun = $this->angka($_POST['id_tahun'] ?? null);
        $idSemester = $this->angka($_POST['id_semester'] ?? null);
        $idKelas = $this->angka($_POST['id_kelas'] ?? null);
        $idMapel = $this->angka($_POST['id_mapel'] ?? null);
        if (!$idTahun || !$idSemester || !$idKelas || !$idMapel) {
            $this->gagalInput($idTahun, $idSemester, $idKelas);
            return;
        }
        $this->flashHasil(
            $this->modelTahunAjaran->hapusPenugasanPersiapan($idTahun, $idSemester, $idKelas, $idMapel),
            'Penugasan guru dan jadwal mapel berhasil dihapus.'
        );
        $this->kembali($idTahun, $idSemester, $idKelas);
    }

    public function simpanJadwal(): void {
        $idTahun = $this->angka($_POST['id_tahun'] ?? null);
        $idSemester = $this->angka($_POST['id_semester'] ?? null);
        $idKelas = $this->angka($_POST['id_kelas'] ?? null);
        $idPenugasan = $this->angka($_POST['id_penugasan'] ?? null);
        if (!$idTahun || !$idSemester || !$idKelas || !$idPenugasan
            || !$this->modelTahunAjaran->penugasanBisaDijadwalkan($idTahun, $idSemester, $idKelas, $idPenugasan)
        ) {
            Session::setFlash('error', 'Penugasan tidak sesuai dengan semester persiapan yang dipilih.');
            $this->kembali($idTahun, $idSemester, $idKelas);
            return;
        }
        $error = $this->modelJadwal->simpanJadwal(
            $idPenugasan,
            $this->teks($_POST['hari'] ?? null),
            $this->teks($_POST['jam_mulai'] ?? null),
            $this->teks($_POST['jam_selesai'] ?? null),
            $this->teks($_POST['ruangan'] ?? null),
            isset($_POST['apakah_lab'])
        );
        $this->flashHasil($error, 'Jadwal berhasil disimpan.');
        $this->kembali($idTahun, $idSemester, $idKelas);
    }

    public function hapusJadwal(): void {
        $idTahun = $this->angka($_POST['id_tahun'] ?? null);
        $idSemester = $this->angka($_POST['id_semester'] ?? null);
        $idKelas = $this->angka($_POST['id_kelas'] ?? null);
        $idJadwal = $this->angka($_POST['id_jadwal'] ?? null);
        if (!$idTahun || !$idSemester || !$idKelas || !$idJadwal) {
            $this->gagalInput($idTahun, $idSemester, $idKelas);
            return;
        }
        $this->flashHasil(
            $this->modelTahunAjaran->hapusJadwalPersiapan($idTahun, $idSemester, $idJadwal),
            'Jadwal berhasil dihapus.'
        );
        $this->kembali($idTahun, $idSemester, $idKelas);
    }

    public function aktifkanGenap(): void {
        $idTahun = $this->angka($_POST['id_tahun'] ?? null);
        if (!$idTahun) {
            $this->gagalInput(null, null, null);
            return;
        }
        $this->flashHasil(
            $this->modelTahunAjaran->aktifkanSemesterGenap($idTahun),
            'Semester Genap berhasil diaktifkan. Kenaikan kelas belum dilakukan.'
        );
        $this->kembali($idTahun, $_POST['id_semester'] ?? null, $_POST['id_kelas'] ?? null);
    }

    public function finalisasiKenaikan(): void {
        $idTahun = $this->angka($_POST['id_tahun'] ?? null);
        if (!$idTahun) {
            $this->gagalInput(null, null, null);
            return;
        }
        $hasil = $this->modelTahunAjaran->finalisasiKenaikan($idTahun);
        if ($hasil['ok']) {
            Session::setFlash('success', $hasil['message']);
        } else {
            Session::setFlash('error', $hasil['message']);
        }
        $this->kembali($idTahun, $_POST['id_semester'] ?? null, $_POST['id_kelas'] ?? null);
    }

    private function angka($nilai): ?int {
        $angka = filter_var($nilai, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $angka === false ? null : $angka;
    }

    private function teks($nilai): string {
        return is_string($nilai) ? trim($nilai) : '';
    }

    private function semesterMilikTahun(int $idTahun, int $idSemester): ?array {
        foreach ($this->modelTahunAjaran->daftarSemester($idTahun) as $semester) {
            if ((int)$semester['id'] === $idSemester) {
                return $semester;
            }
        }
        return null;
    }

    private function flashHasil(?string $error, string $success): void {
        Session::setFlash($error === null ? 'success' : 'error', $error ?? $success);
    }

    private function gagalInput($idTahun, $idSemester, $idKelas): void {
        Session::setFlash('error', 'Data formulir tidak lengkap atau tidak valid.');
        $this->kembali($idTahun, $idSemester, $idKelas);
    }

    private function kembali($idTahun, $idSemester, $idKelas): void {
        $query = [];
        $tahun = $this->angka($idTahun);
        $semester = $this->angka($idSemester);
        $kelas = $this->angka($idKelas);
        if ($tahun !== null) $query['tahun'] = $tahun;
        if ($semester !== null) $query['semester'] = $semester;
        if ($kelas !== null) $query['kelas'] = $kelas;
        $this->redirect('/kesiswaan/tahun-ajaran' . ($query ? '?' . http_build_query($query) : ''));
    }
}
