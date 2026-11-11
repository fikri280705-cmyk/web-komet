<?php
/**
 * Pengelolaan tahun ajaran, semester, kurikulum, dan kenaikan kelas.
 */

namespace App\Models;

use App\Core\Model;
use DateTimeImmutable;
use InvalidArgumentException;
use Throwable;

class TahunAjaran extends Model {
    public function daftarTahunAjaran(): array {
        return $this->fetchAll(
            "SELECT ta.*,
                    MAX(CASE WHEN sem.apakah_aktif = 1 THEN sem.id END) AS id_semester_aktif,
                    MAX(CASE WHEN sem.apakah_aktif = 1 THEN sem.jenis_semester END) AS semester_aktif
             FROM tahun_ajaran ta
             LEFT JOIN semester sem ON sem.id_tahun_ajaran = ta.id
             GROUP BY ta.id
             ORDER BY ta.tanggal_mulai DESC"
        );
    }

    private function ambilPeriodeAktifTerkunci(): ?array {
        return $this->fetchOne(
            "SELECT ta.id AS id_tahun_ajaran, ta.kode_tahun, ta.nama_tahun,
                    ta.tanggal_mulai AS tahun_mulai, ta.tanggal_selesai AS tahun_selesai,
                    sem.id AS id_semester, sem.jenis_semester,
                    sem.tanggal_mulai AS semester_mulai, sem.tanggal_selesai AS semester_selesai
             FROM tahun_ajaran ta
             JOIN semester sem ON sem.id_tahun_ajaran = ta.id
             WHERE ta.apakah_aktif = 1 AND sem.apakah_aktif = 1
             LIMIT 1
             FOR UPDATE"
        );
    }

    public function ambilPeriodeAktif(): ?array {
        return $this->fetchOne(
            "SELECT ta.id AS id_tahun_ajaran, ta.kode_tahun, ta.nama_tahun,
                    ta.tanggal_mulai AS tahun_mulai, ta.tanggal_selesai AS tahun_selesai,
                    sem.id AS id_semester, sem.jenis_semester,
                    sem.tanggal_mulai AS semester_mulai, sem.tanggal_selesai AS semester_selesai
             FROM tahun_ajaran ta
             JOIN semester sem ON sem.id_tahun_ajaran = ta.id
             WHERE ta.apakah_aktif = 1 AND sem.apakah_aktif = 1
             LIMIT 1"
        );
    }

    public function daftarSemester(int $idTahunAjaran): array {
        return $this->fetchAll(
            "SELECT id, id_tahun_ajaran, jenis_semester, apakah_aktif, tanggal_mulai, tanggal_selesai
             FROM semester
             WHERE id_tahun_ajaran = :id_tahun
             ORDER BY tanggal_mulai ASC",
            [':id_tahun' => $idTahunAjaran]
        );
    }

    public function buatDraft(array $data): int {
        $tanggalTahunMulai = $this->tanggalValid($this->tanggalInput($data, 'tanggal_mulai'));
        $tanggalTahunSelesai = $this->tanggalValid($this->tanggalInput($data, 'tanggal_selesai'));
        $tanggalGanjilMulai = $this->tanggalValid($this->tanggalInput($data, 'ganjil_mulai'));
        $tanggalGanjilSelesai = $this->tanggalValid($this->tanggalInput($data, 'ganjil_selesai'));
        $tanggalGenapMulai = $this->tanggalValid($this->tanggalInput($data, 'genap_mulai'));
        $tanggalGenapSelesai = $this->tanggalValid($this->tanggalInput($data, 'genap_selesai'));

        if ($tanggalTahunMulai >= $tanggalTahunSelesai
            || $tanggalGanjilMulai < $tanggalTahunMulai
            || $tanggalGanjilSelesai >= $tanggalGenapMulai
            || $tanggalGenapSelesai > $tanggalTahunSelesai
            || $tanggalGanjilMulai >= $tanggalGanjilSelesai
            || $tanggalGenapMulai >= $tanggalGenapSelesai
        ) {
            throw new InvalidArgumentException('Rentang tanggal tahun ajaran dan semester tidak valid atau saling tumpang tindih.');
        }

        $kodeTahun = $tanggalTahunMulai->format('Y') . '-' . $tanggalTahunSelesai->format('Y');
        if ((int)$tanggalTahunSelesai->format('Y') !== (int)$tanggalTahunMulai->format('Y') + 1) {
            throw new InvalidArgumentException('Tahun ajaran harus mencakup dua tahun kalender yang berurutan.');
        }

        $this->db->beginTransaction();
        try {
            $tahunAktif = $this->query(
                "SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 ORDER BY id FOR UPDATE"
            )->fetchAll();
            $semesterAktif = $this->query(
                "SELECT id FROM semester WHERE apakah_aktif = 1 ORDER BY id FOR UPDATE"
            )->fetchAll();
            if (count($tahunAktif) !== 1 || count($semesterAktif) !== 1) {
                throw new InvalidArgumentException('Status periode tidak konsisten: harus ada tepat satu tahun ajaran dan satu semester aktif.');
            }
            $periodeAktif = $this->ambilPeriodeAktif();
            if (!$periodeAktif) {
                throw new InvalidArgumentException('Tidak ada tahun ajaran dan semester aktif yang valid.');
            }
            if ($tanggalTahunMulai->format('Y-m-d') <= $periodeAktif['tahun_selesai']
                || (int)$tanggalTahunMulai->format('Y') !== (int)$periodeAktif['tahun_mulai'] + 1
            ) {
                throw new InvalidArgumentException('Tahun ajaran baru harus dimulai setelah tahun aktif berakhir dan berada pada tahun kalender berikutnya.');
            }
            if ($this->fetchColumn(
                "SELECT id FROM tahun_ajaran WHERE kode_tahun = :kode LIMIT 1",
                [':kode' => $kodeTahun]
            ) !== false) {
                throw new InvalidArgumentException("Tahun ajaran {$kodeTahun} sudah terdaftar.");
            }

            $this->query(
                "INSERT INTO tahun_ajaran (kode_tahun, nama_tahun, apakah_aktif, tanggal_mulai, tanggal_selesai)
                 VALUES (:kode, :nama, 0, :mulai, :selesai)",
                [
                    ':kode' => $kodeTahun,
                    ':nama' => 'Tahun Ajaran ' . $tanggalTahunMulai->format('Y') . '/' . $tanggalTahunSelesai->format('Y'),
                    ':mulai' => $tanggalTahunMulai->format('Y-m-d'),
                    ':selesai' => $tanggalTahunSelesai->format('Y-m-d')
                ]
            );
            $idTahunAjaran = $this->lastInsertId();

            foreach ([
                ['jenis' => 'ganjil', 'mulai' => $tanggalGanjilMulai, 'selesai' => $tanggalGanjilSelesai],
                ['jenis' => 'genap', 'mulai' => $tanggalGenapMulai, 'selesai' => $tanggalGenapSelesai]
            ] as $semester) {
                $this->query(
                    "INSERT INTO semester (id_tahun_ajaran, jenis_semester, apakah_aktif, tanggal_mulai, tanggal_selesai)
                     VALUES (:id_tahun, :jenis, 0, :mulai, :selesai)",
                    [
                        ':id_tahun' => $idTahunAjaran,
                        ':jenis' => $semester['jenis'],
                        ':mulai' => $semester['mulai']->format('Y-m-d'),
                        ':selesai' => $semester['selesai']->format('Y-m-d')
                    ]
                );
            }

            $this->db->commit();
            return $idTahunAjaran;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function daftarJurusan(): array {
        return $this->fetchAll("SELECT id, kode_jurusan, nama_jurusan FROM jurusan ORDER BY nama_jurusan");
    }

    public function daftarKelas(): array {
        return $this->fetchAll(
            "SELECT k.id, k.id_jurusan, k.tingkat, k.nomor_rombel, k.nama_kelas,
                    j.nama_jurusan, j.kode_jurusan
             FROM kelas k JOIN jurusan j ON j.id = k.id_jurusan
             ORDER BY k.tingkat, j.nama_jurusan, k.nomor_rombel"
        );
    }

    public function daftarMapel(int $idJurusan): array {
        return $this->fetchAll(
            "SELECT id, kode_mapel, nama_mapel, kelompok_mapel, id_jurusan
             FROM mata_pelajaran
             WHERE id_jurusan IS NULL OR id_jurusan = :id_jurusan
             ORDER BY kelompok_mapel, nama_mapel",
            [':id_jurusan' => $idJurusan]
        );
    }

    public function simpanTemplate(
        int $idTahunAjaran,
        int $idJurusan,
        int $tingkat,
        string $jenisSemester,
        int $idMapel,
        int $pertemuanPerMinggu
    ): ?string {
        if (!in_array($tingkat, [10, 11, 12], true)
            || !in_array($jenisSemester, ['ganjil', 'genap'], true)
            || $pertemuanPerMinggu < 1
            || $pertemuanPerMinggu > 25
        ) {
            return 'Data kurikulum tidak valid. Pertemuan mingguan harus 1 sampai 25.';
        }

        $this->db->beginTransaction();
        try {
            if (!$this->semesterBisaDisiapkan($idTahunAjaran, $jenisSemester, true)) {
                $this->db->rollBack();
                return 'Kurikulum hanya dapat diubah pada semester yang belum aktif.';
            }
            $mapel = $this->fetchOne(
                "SELECT id, id_jurusan FROM mata_pelajaran WHERE id = :id_mapel",
                [':id_mapel' => $idMapel]
            );
            if (!$mapel || ($mapel['id_jurusan'] !== null && (int)$mapel['id_jurusan'] !== $idJurusan)) {
                $this->db->rollBack();
                return 'Mata pelajaran tidak sesuai dengan jurusan yang dipilih.';
            }
            $this->query(
                "INSERT INTO kurikulum_mapel
                    (id_tahun_ajaran, id_jurusan, tingkat, jenis_semester, id_mapel, pertemuan_per_minggu)
                 VALUES (:id_tahun, :id_jurusan, :tingkat, :semester, :id_mapel, :pertemuan)
                 ON DUPLICATE KEY UPDATE pertemuan_per_minggu = VALUES(pertemuan_per_minggu)",
                [
                    ':id_tahun' => $idTahunAjaran,
                    ':id_jurusan' => $idJurusan,
                    ':tingkat' => $tingkat,
                    ':semester' => $jenisSemester,
                    ':id_mapel' => $idMapel,
                    ':pertemuan' => $pertemuanPerMinggu
                ]
            );
            $this->db->commit();
            return null;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function imporPersiapan(int $idTahunAjaran, array $rows): array {
        $this->db->beginTransaction();
        try {
            $tahun = $this->fetchOne(
                "SELECT id FROM tahun_ajaran WHERE id = :id FOR UPDATE",
                [':id' => $idTahunAjaran]
            );
            if (!$tahun) {
                $this->db->rollBack();
                return ['errors' => ['Tahun ajaran yang dipilih tidak ditemukan.']];
            }

            $errors = [];
            $resolved = [];
            $seen = [];
            $semesterIds = [];
            $hasWali = false;
            foreach ($rows as $row) {
                $line = (int)($row['_baris'] ?? 0);
                $type = strtolower(trim((string)($row['tipe_data'] ?? '')));
                $semesterType = strtolower(trim((string)($row['semester'] ?? '')));
                $className = trim((string)($row['nama_kelas'] ?? ''));
                $departmentCode = trim((string)($row['kode_jurusan'] ?? ''));
                $courseCode = trim((string)($row['kode_mapel'] ?? ''));
                $teacherNumber = trim((string)($row['nuptk'] ?? ''));
                $semesterId = null;
                $class = null;
                $department = null;
                $course = null;
                $teacher = null;

                if (!in_array($type, ['wali_kelas', 'kurikulum', 'pengajar', 'jadwal'], true)) {
                    $errors[] = "Baris {$line}: tipe_data harus wali_kelas, kurikulum, pengajar, atau jadwal.";
                    continue;
                }
                if ($type === 'wali_kelas') {
                    $hasWali = true;
                } elseif (!in_array($semesterType, ['ganjil', 'genap'], true)) {
                    $errors[] = "Baris {$line}: semester harus ganjil atau genap.";
                    continue;
                } else {
                    if (!array_key_exists($semesterType, $semesterIds)) {
                        $semester = $this->fetchOne(
                            "SELECT id FROM semester
                             WHERE id_tahun_ajaran = :id_tahun AND jenis_semester = :jenis",
                            [':id_tahun' => $idTahunAjaran, ':jenis' => $semesterType]
                        );
                        $semesterIds[$semesterType] = $semester ? (int)$semester['id'] : null;
                    }
                    $semesterId = $semesterIds[$semesterType];
                    if ($semesterId === null
                        || !$this->semesterBisaDisiapkan($idTahunAjaran, $semesterType, true)
                    ) {
                        $errors[] = "Baris {$line}: semester {$semesterType} tidak dapat disiapkan pada tahun ajaran ini.";
                        continue;
                    }
                }

                if ($type === 'kurikulum') {
                    $level = filter_var($row['tingkat'] ?? null, FILTER_VALIDATE_INT);
                    $meetings = filter_var($row['pertemuan_per_minggu'] ?? null, FILTER_VALIDATE_INT);
                    if ($departmentCode === '' || !in_array($level, [10, 11, 12], true)
                        || $courseCode === '' || $meetings === false || $meetings < 1 || $meetings > 25
                    ) {
                        $errors[] = "Baris {$line}: kurikulum memerlukan kode_jurusan, tingkat 10-12, kode_mapel, dan pertemuan_per_minggu 1-25.";
                        continue;
                    }
                    $department = $this->fetchOne(
                        "SELECT id FROM jurusan WHERE UPPER(kode_jurusan) = UPPER(:kode)",
                        [':kode' => $departmentCode]
                    );
                    $course = $this->fetchOne(
                        "SELECT id, id_jurusan FROM mata_pelajaran
                         WHERE UPPER(kode_mapel) = UPPER(:kode)",
                        [':kode' => $courseCode]
                    );
                    if (!$department || !$course
                        || ($course['id_jurusan'] !== null && (int)$course['id_jurusan'] !== (int)$department['id'])
                    ) {
                        $errors[] = "Baris {$line}: jurusan atau mata pelajaran tidak ditemukan atau tidak sesuai.";
                        continue;
                    }
                    $key = implode('|', [$type, $semesterType, (int)$department['id'], $level, (int)$course['id']]);
                    $entry = [
                        'type' => $type,
                        'line' => $line,
                        'semester_type' => $semesterType,
                        'semester_id' => $semesterId,
                        'department_id' => (int)$department['id'],
                        'level' => $level,
                        'course_id' => (int)$course['id'],
                        'meetings' => $meetings
                    ];
                } else {
                    if ($className === '') {
                        $errors[] = "Baris {$line}: nama_kelas wajib diisi.";
                        continue;
                    }
                    $class = $this->fetchOne(
                        "SELECT k.id, k.id_jurusan, k.tingkat, j.kode_jurusan
                         FROM kelas k
                         JOIN jurusan j ON j.id = k.id_jurusan
                         WHERE UPPER(k.nama_kelas) = UPPER(:nama)",
                        [':nama' => $className]
                    );
                    if (!$class
                        || ($departmentCode !== '' && strcasecmp($departmentCode, $class['kode_jurusan']) !== 0)
                        || (trim((string)($row['tingkat'] ?? '')) !== ''
                            && (int)$row['tingkat'] !== (int)$class['tingkat'])
                    ) {
                        $errors[] = "Baris {$line}: kelas tidak ditemukan atau kode_jurusan/tingkat tidak sesuai.";
                        continue;
                    }

                    if ($type === 'wali_kelas') {
                        if ($teacherNumber === '') {
                            $errors[] = "Baris {$line}: NUPTK wajib diisi untuk wali kelas.";
                            continue;
                        }
                        $teacher = $this->fetchOne(
                            "SELECT id FROM guru WHERE nuptk = :nuptk AND apakah_aktif = 1",
                            [':nuptk' => $teacherNumber]
                        );
                        if (!$teacher) {
                            $errors[] = "Baris {$line}: guru aktif dengan NUPTK tersebut tidak ditemukan.";
                            continue;
                        }
                        $key = implode('|', [$type, (int)$class['id']]);
                        $entry = [
                            'type' => $type,
                            'line' => $line,
                            'class_id' => (int)$class['id'],
                            'teacher_id' => (int)$teacher['id']
                        ];
                    } else {
                        if ($courseCode === '') {
                            $errors[] = "Baris {$line}: kode_mapel wajib diisi.";
                            continue;
                        }
                        $course = $this->fetchOne(
                            "SELECT id, id_jurusan FROM mata_pelajaran
                             WHERE UPPER(kode_mapel) = UPPER(:kode)",
                            [':kode' => $courseCode]
                        );
                        if (!$course
                            || ($course['id_jurusan'] !== null && (int)$course['id_jurusan'] !== (int)$class['id_jurusan'])
                        ) {
                            $errors[] = "Baris {$line}: mata pelajaran tidak ditemukan atau tidak sesuai dengan jurusan kelas.";
                            continue;
                        }
                        $entry = [
                            'type' => $type,
                            'line' => $line,
                            'semester_type' => $semesterType,
                            'semester_id' => $semesterId,
                            'class_id' => (int)$class['id'],
                            'course_id' => (int)$course['id']
                        ];

                        if ($type === 'pengajar') {
                            if ($teacherNumber === '') {
                                $errors[] = "Baris {$line}: NUPTK wajib diisi untuk pengajar.";
                                continue;
                            }
                            $teacher = $this->fetchOne(
                                "SELECT id FROM guru WHERE nuptk = :nuptk AND apakah_aktif = 1",
                                [':nuptk' => $teacherNumber]
                            );
                            if (!$teacher) {
                                $errors[] = "Baris {$line}: guru aktif dengan NUPTK tersebut tidak ditemukan.";
                                continue;
                            }
                            $entry['teacher_id'] = (int)$teacher['id'];
                            $key = implode('|', [$type, $semesterType, (int)$class['id'], (int)$course['id']]);
                        } else {
                            $day = trim((string)($row['hari'] ?? ''));
                            $start = trim((string)($row['jam_mulai'] ?? ''));
                            $end = trim((string)($row['jam_selesai'] ?? ''));
                            $room = trim((string)($row['ruangan'] ?? ''));
                            $labValue = trim((string)($row['apakah_lab'] ?? ''));
                            $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
                            if (!in_array($day, $days, true)
                                || !$this->waktuJadwalValid($start, $end)
                                || $room === '' || strlen($room) > 50
                                || !in_array($labValue, ['0', '1'], true)
                            ) {
                                $errors[] = "Baris {$line}: jadwal memerlukan hari kerja, jam HH:MM yang valid, ruangan, dan apakah_lab 0 atau 1.";
                                continue;
                            }
                            $entry['day'] = $day;
                            $entry['start'] = $start;
                            $entry['end'] = $end;
                            $entry['room'] = $room;
                            $entry['is_lab'] = (int)$labValue;
                            $key = implode('|', [
                                $type,
                                $semesterType,
                                (int)$class['id'],
                                (int)$course['id'],
                                $day,
                                $start,
                                $end,
                                strtolower($room)
                            ]);
                        }
                    }
                }

                if (isset($seen[$key])) {
                    $errors[] = "Baris {$line}: data yang sama muncul lebih dari sekali (pertama pada baris {$seen[$key]}).";
                    continue;
                }
                $seen[$key] = $line;
                $resolved[] = $entry;
            }

            if ($hasWali && !$this->kunciTahunTidakAktif($idTahunAjaran)) {
                $errors[] = 'Penugasan wali kelas hanya dapat diimpor untuk tahun ajaran mendatang yang belum aktif.';
            }

            $templatesDariImpor = [];
            $penugasanDariImpor = [];
            foreach ($resolved as $entry) {
                if ($entry['type'] === 'kurikulum') {
                    $templatesDariImpor[implode('|', [
                        $entry['semester_type'],
                        $entry['department_id'],
                        $entry['level'],
                        $entry['course_id']
                    ])] = true;
                } elseif ($entry['type'] === 'pengajar') {
                    $penugasanDariImpor[implode('|', [
                        $entry['semester_id'],
                        $entry['class_id'],
                        $entry['course_id']
                    ])] = $entry;
                }
            }

            foreach ($resolved as $entry) {
                if (!in_array($entry['type'], ['pengajar', 'jadwal'], true)) {
                    continue;
                }
                $class = $this->fetchOne(
                    "SELECT id_jurusan, tingkat FROM kelas WHERE id = :id",
                    [':id' => $entry['class_id']]
                );
                $templateKey = implode('|', [
                    $entry['semester_type'],
                    (int)$class['id_jurusan'],
                    (int)$class['tingkat'],
                    $entry['course_id']
                ]);
                $templateExists = isset($templatesDariImpor[$templateKey])
                    || $this->fetchColumn(
                        "SELECT id FROM kurikulum_mapel
                         WHERE id_tahun_ajaran = :id_tahun AND id_jurusan = :id_jurusan
                           AND tingkat = :tingkat AND jenis_semester = :jenis AND id_mapel = :id_mapel",
                        [
                            ':id_tahun' => $idTahunAjaran,
                            ':id_jurusan' => (int)$class['id_jurusan'],
                            ':tingkat' => (int)$class['tingkat'],
                            ':jenis' => $entry['semester_type'],
                            ':id_mapel' => $entry['course_id']
                        ]
                    ) !== false;
                if (!$templateExists) {
                    $errors[] = "Baris {$entry['line']}: kurikulum mapel belum disiapkan untuk kelas dan semester ini.";
                    continue;
                }
                $assignmentKey = implode('|', [
                    $entry['semester_id'],
                    $entry['class_id'],
                    $entry['course_id']
                ]);
                if ($entry['type'] === 'jadwal' && !isset($penugasanDariImpor[$assignmentKey])) {
                    $assignments = $this->fetchAll(
                        "SELECT pg.id, g.apakah_aktif AS guru_aktif
                         FROM penugasan_guru pg
                         JOIN guru g ON g.id = pg.id_guru
                         WHERE pg.id_tahun_ajaran = :id_tahun AND pg.id_semester = :id_semester
                           AND pg.id_kelas = :id_kelas AND pg.id_mapel = :id_mapel",
                        [
                            ':id_tahun' => $idTahunAjaran,
                            ':id_semester' => $entry['semester_id'],
                            ':id_kelas' => $entry['class_id'],
                            ':id_mapel' => $entry['course_id']
                        ]
                    );
                    if (count($assignments) !== 1 || (int)$assignments[0]['guru_aktif'] !== 1) {
                        $errors[] = "Baris {$entry['line']}: jadwal memerlukan satu penugasan pengajar aktif yang sudah ada atau baris pengajar di file.";
                    }
                }
            }

            $waliByClass = [];
            foreach ($this->fetchAll(
                "SELECT id_guru, id_kelas FROM penugasan_wali_kelas
                 WHERE id_tahun_ajaran = :id_tahun AND apakah_aktif = 1",
                [':id_tahun' => $idTahunAjaran]
            ) as $wali) {
                $waliByClass[(int)$wali['id_kelas']] = (int)$wali['id_guru'];
            }
            $waliBarisPerKelas = [];
            foreach ($resolved as $entry) {
                if ($entry['type'] !== 'wali_kelas') {
                    continue;
                }
                $waliByClass[$entry['class_id']] = $entry['teacher_id'];
                $waliBarisPerKelas[$entry['class_id']] = $entry['line'];
            }
            $waliKelasPerGuru = [];
            foreach ($waliByClass as $classId => $teacherId) {
                if (isset($waliKelasPerGuru[$teacherId])) {
                    $classSebelumnya = $waliKelasPerGuru[$teacherId];
                    $line = $waliBarisPerKelas[$classId] ?? $waliBarisPerKelas[$classSebelumnya] ?? null;
                    if ($line !== null) {
                        $errors[] = "Baris {$line}: guru tersebut sudah menjadi wali kelas lain pada tahun ajaran ini.";
                    }
                    continue;
                }
                $waliKelasPerGuru[$teacherId] = $classId;
            }

            foreach ($resolved as $entry) {
                if ($entry['type'] !== 'pengajar') {
                    continue;
                }
                $existing = $this->fetchAll(
                    "SELECT id, id_guru
                     FROM penugasan_guru
                     WHERE id_tahun_ajaran = :id_tahun AND id_semester = :id_semester
                       AND id_kelas = :id_kelas AND id_mapel = :id_mapel",
                    [
                        ':id_tahun' => $idTahunAjaran,
                        ':id_semester' => $entry['semester_id'],
                        ':id_kelas' => $entry['class_id'],
                        ':id_mapel' => $entry['course_id']
                    ]
                );
                if (count($existing) > 1) {
                    $errors[] = "Baris {$entry['line']}: terdapat lebih dari satu penugasan guru untuk kelas dan mapel tersebut; rapikan data terlebih dahulu.";
                } elseif ($existing && (int)$existing[0]['id_guru'] !== $entry['teacher_id']
                    && $this->fetchColumn(
                        "SELECT id FROM jadwal_pelajaran WHERE id_penugasan_guru = :id LIMIT 1",
                        [':id' => (int)$existing[0]['id']]
                    ) !== false
                ) {
                    $errors[] = "Baris {$entry['line']}: hapus jadwal lama sebelum mengganti guru pengajar.";
                }
            }

            if ($errors !== []) {
                $this->db->rollBack();
                return ['errors' => array_slice($errors, 0, 50)];
            }

            $counts = ['wali_kelas' => 0, 'kurikulum' => 0, 'pengajar' => 0, 'jadwal' => 0];
            foreach ($resolved as $entry) {
                if ($entry['type'] !== 'wali_kelas') {
                    continue;
                }
                $this->query(
                    "INSERT INTO penugasan_wali_kelas
                        (id_guru, id_kelas, id_tahun_ajaran, apakah_aktif, tanggal_mulai_tugas)
                     VALUES (:id_guru, :id_kelas, :id_tahun, 1, CURDATE())
                     ON DUPLICATE KEY UPDATE id_guru = VALUES(id_guru), apakah_aktif = 1",
                    [
                        ':id_guru' => $entry['teacher_id'],
                        ':id_kelas' => $entry['class_id'],
                        ':id_tahun' => $idTahunAjaran
                    ]
                );
                $counts['wali_kelas']++;
            }
            foreach ($resolved as $entry) {
                if ($entry['type'] !== 'kurikulum') {
                    continue;
                }
                $this->query(
                    "INSERT INTO kurikulum_mapel
                        (id_tahun_ajaran, id_jurusan, tingkat, jenis_semester, id_mapel, pertemuan_per_minggu)
                     VALUES (:id_tahun, :id_jurusan, :tingkat, :semester, :id_mapel, :pertemuan)
                     ON DUPLICATE KEY UPDATE pertemuan_per_minggu = VALUES(pertemuan_per_minggu)",
                    [
                        ':id_tahun' => $idTahunAjaran,
                        ':id_jurusan' => $entry['department_id'],
                        ':tingkat' => $entry['level'],
                        ':semester' => $entry['semester_type'],
                        ':id_mapel' => $entry['course_id'],
                        ':pertemuan' => $entry['meetings']
                    ]
                );
                $counts['kurikulum']++;
            }
            foreach ($resolved as $entry) {
                if ($entry['type'] !== 'pengajar') {
                    continue;
                }
                $existing = $this->fetchAll(
                    "SELECT id, id_guru
                     FROM penugasan_guru
                     WHERE id_tahun_ajaran = :id_tahun AND id_semester = :id_semester
                       AND id_kelas = :id_kelas AND id_mapel = :id_mapel",
                    [
                        ':id_tahun' => $idTahunAjaran,
                        ':id_semester' => $entry['semester_id'],
                        ':id_kelas' => $entry['class_id'],
                        ':id_mapel' => $entry['course_id']
                    ]
                );
                if ($existing) {
                    $this->query(
                        "UPDATE penugasan_guru SET id_guru = :id_guru WHERE id = :id",
                        [':id_guru' => $entry['teacher_id'], ':id' => (int)$existing[0]['id']]
                    );
                } else {
                    $this->query(
                        "INSERT INTO penugasan_guru
                            (id_tahun_ajaran, id_semester, id_guru, id_mapel, id_kelas)
                         VALUES (:id_tahun, :id_semester, :id_guru, :id_mapel, :id_kelas)",
                        [
                            ':id_tahun' => $idTahunAjaran,
                            ':id_semester' => $entry['semester_id'],
                            ':id_guru' => $entry['teacher_id'],
                            ':id_mapel' => $entry['course_id'],
                            ':id_kelas' => $entry['class_id']
                        ]
                    );
                }
                $counts['pengajar']++;
            }

            $jadwalModel = new Jadwal();
            foreach ($resolved as $entry) {
                if ($entry['type'] !== 'jadwal') {
                    continue;
                }
                $assignment = $this->fetchOne(
                    "SELECT id FROM penugasan_guru
                     WHERE id_tahun_ajaran = :id_tahun AND id_semester = :id_semester
                       AND id_kelas = :id_kelas AND id_mapel = :id_mapel",
                    [
                        ':id_tahun' => $idTahunAjaran,
                        ':id_semester' => $entry['semester_id'],
                        ':id_kelas' => $entry['class_id'],
                        ':id_mapel' => $entry['course_id']
                    ]
                );
                if (!$assignment) {
                    $errors[] = "Baris {$entry['line']}: penugasan pengajar tidak ditemukan.";
                    break;
                }
                $assignmentId = (int)$assignment['id'];
                $conflict = $jadwalModel->cekBentrok(
                    $assignmentId,
                    $entry['day'],
                    $entry['start'],
                    $entry['end'],
                    $entry['room']
                );
                if ($conflict !== null) {
                    $errors[] = "Baris {$entry['line']}: {$conflict}";
                    break;
                }
                $this->query(
                    "INSERT INTO jadwal_pelajaran
                        (id_penugasan_guru, hari, jam_mulai, jam_selesai, ruangan, apakah_lab)
                     VALUES (:id_penugasan, :hari, :mulai, :selesai, :ruangan, :apakah_lab)",
                    [
                        ':id_penugasan' => $assignmentId,
                        ':hari' => $entry['day'],
                        ':mulai' => $entry['start'],
                        ':selesai' => $entry['end'],
                        ':ruangan' => $entry['room'],
                        ':apakah_lab' => $entry['is_lab']
                    ]
                );
                $counts['jadwal']++;
            }

            if ($errors !== []) {
                $this->db->rollBack();
                return ['errors' => array_slice($errors, 0, 50)];
            }
            $this->db->commit();
            return ['errors' => [], 'counts' => $counts];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function waktuJadwalValid(string $mulai, string $selesai): bool {
        $format = '/^(?:[01]\d|2[0-3]):[0-5]\d$/';
        if (!preg_match($format, $mulai) || !preg_match($format, $selesai)) {
            return false;
        }
        return $mulai < $selesai;
    }

    public function hapusTemplate(int $idTemplate): ?string {
        $template = $this->fetchOne(
            "SELECT km.* FROM kurikulum_mapel km WHERE km.id = :id",
            [':id' => $idTemplate]
        );
        if (!$template) {
            return 'Template kurikulum tidak ditemukan.';
        }
        $this->db->beginTransaction();
        try {
            $semester = $this->fetchOne(
                "SELECT id FROM semester
                 WHERE id_tahun_ajaran = :id_tahun AND jenis_semester = :jenis",
                [':id_tahun' => (int)$template['id_tahun_ajaran'], ':jenis' => $template['jenis_semester']]
            );
            if (!$semester || !$this->kunciSemesterBelumAktif(
                (int)$template['id_tahun_ajaran'],
                (int)$semester['id']
            )) {
                $this->db->rollBack();
                return 'Kurikulum hanya dapat diubah pada semester yang belum aktif.';
            }
            if ($this->fetchColumn(
                "SELECT pg.id
                 FROM penugasan_guru pg
                 JOIN kelas k ON k.id = pg.id_kelas
                 WHERE pg.id_tahun_ajaran = :id_tahun
                   AND pg.id_semester = :id_semester
                   AND k.id_jurusan = :id_jurusan
                   AND k.tingkat = :tingkat
                   AND pg.id_mapel = :id_mapel
                 LIMIT 1",
                [
                    ':id_tahun' => (int)$template['id_tahun_ajaran'],
                    ':id_semester' => (int)$semester['id'],
                    ':id_jurusan' => (int)$template['id_jurusan'],
                    ':tingkat' => (int)$template['tingkat'],
                    ':id_mapel' => (int)$template['id_mapel']
                ]
            ) !== false) {
                $this->db->rollBack();
                return 'Hapus penugasan dan jadwal mapel ini sebelum menghapus template kurikulum.';
            }

            $this->query("DELETE FROM kurikulum_mapel WHERE id = :id", [':id' => $idTemplate]);
            $this->db->commit();
            return null;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function ambilTemplateKurikulum(int $idTahunAjaran, string $jenisSemester, ?int $idJurusan = null, ?int $tingkat = null): array {
        $filters = ['km.id_tahun_ajaran = :id_tahun', 'km.jenis_semester = :semester'];
        $params = [':id_tahun' => $idTahunAjaran, ':semester' => $jenisSemester];
        if ($idJurusan !== null) {
            $filters[] = 'km.id_jurusan = :id_jurusan';
            $params[':id_jurusan'] = $idJurusan;
        }
        if ($tingkat !== null) {
            $filters[] = 'km.tingkat = :tingkat';
            $params[':tingkat'] = $tingkat;
        }

        return $this->fetchAll(
            "SELECT km.*, j.kode_jurusan, j.nama_jurusan, mp.nama_mapel, mp.kode_mapel
             FROM kurikulum_mapel km
             JOIN jurusan j ON j.id = km.id_jurusan
             JOIN mata_pelajaran mp ON mp.id = km.id_mapel
             WHERE " . implode(' AND ', $filters) . "
             ORDER BY j.nama_jurusan, km.tingkat, mp.nama_mapel",
            $params
        );
    }

    public function ambilSetupKelasSemester(int $idTahunAjaran, int $idSemester, int $idKelas): array {
        return $this->fetchAll(
            "SELECT km.id AS id_template, km.id_mapel, km.pertemuan_per_minggu,
                    mp.nama_mapel, mp.kode_mapel,
                    COUNT(DISTINCT pg.id) AS jumlah_penugasan,
                    MIN(pg.id) AS id_penugasan,
                    MAX(pg.id_guru) AS id_guru,
                    MAX(g.nama_lengkap) AS nama_guru,
                    (SELECT COUNT(*)
                     FROM jadwal_pelajaran jp
                     JOIN penugasan_guru pg2 ON pg2.id = jp.id_penugasan_guru
                     WHERE pg2.id_tahun_ajaran = km.id_tahun_ajaran
                       AND pg2.id_semester = :id_semester_jadwal
                       AND pg2.id_kelas = :id_kelas_jadwal
                       AND pg2.id_mapel = km.id_mapel) AS jumlah_jadwal
             FROM kurikulum_mapel km
             JOIN kelas k ON k.id_jurusan = km.id_jurusan AND k.tingkat = km.tingkat
             JOIN mata_pelajaran mp ON mp.id = km.id_mapel
             LEFT JOIN penugasan_guru pg
               ON pg.id_tahun_ajaran = km.id_tahun_ajaran
              AND pg.id_semester = :id_semester
              AND pg.id_kelas = k.id
              AND pg.id_mapel = km.id_mapel
             LEFT JOIN guru g ON g.id = pg.id_guru
             WHERE km.id_tahun_ajaran = :id_tahun
               AND km.jenis_semester = (SELECT jenis_semester FROM semester WHERE id = :id_semester_jenis)
               AND k.id = :id_kelas
             GROUP BY km.id, km.id_mapel, km.pertemuan_per_minggu, mp.nama_mapel, mp.kode_mapel
             ORDER BY mp.nama_mapel",
            [
                ':id_semester_jadwal' => $idSemester,
                ':id_kelas_jadwal' => $idKelas,
                ':id_semester' => $idSemester,
                ':id_tahun' => $idTahunAjaran,
                ':id_semester_jenis' => $idSemester,
                ':id_kelas' => $idKelas
            ]
        );
    }

    public function ambilJadwalPenugasan(int $idPenugasan): array {
        return $this->fetchAll(
            "SELECT id, hari, jam_mulai, jam_selesai, ruangan, apakah_lab
             FROM jadwal_pelajaran
             WHERE id_penugasan_guru = :id
             ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'), jam_mulai",
            [':id' => $idPenugasan]
        );
    }

    public function penugasanBisaDijadwalkan(int $idTahunAjaran, int $idSemester, int $idKelas, int $idPenugasan): bool {
        return $this->fetchColumn(
            "SELECT pg.id
             FROM penugasan_guru pg
             WHERE pg.id = :id_penugasan
               AND pg.id_tahun_ajaran = :id_tahun
               AND pg.id_semester = :id_semester
               AND pg.id_kelas = :id_kelas
               AND EXISTS (
                   SELECT 1 FROM semester sem
                   JOIN tahun_ajaran ta ON ta.id = sem.id_tahun_ajaran
                   WHERE sem.id = pg.id_semester
                     AND sem.id_tahun_ajaran = pg.id_tahun_ajaran
                     AND sem.apakah_aktif = 0
                     AND (
                         (ta.apakah_aktif = 0 AND ta.tanggal_mulai > (
                             SELECT tanggal_selesai FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1
                         ))
                         OR (
                             ta.apakah_aktif = 1
                             AND sem.jenis_semester = 'genap'
                             AND EXISTS (
                                 SELECT 1 FROM semester aktif
                                 WHERE aktif.id_tahun_ajaran = ta.id
                                   AND aktif.jenis_semester = 'ganjil'
                                   AND aktif.apakah_aktif = 1
                             )
                         )
                     )
               )",
            [
                ':id_penugasan' => $idPenugasan,
                ':id_tahun' => $idTahunAjaran,
                ':id_semester' => $idSemester,
                ':id_kelas' => $idKelas
            ]
        ) !== false;
    }

    public function semesterDapatDisiapkan(int $idTahunAjaran, int $idSemester): bool {
        $semester = $this->fetchOne(
            "SELECT jenis_semester
             FROM semester
             WHERE id = :id_semester AND id_tahun_ajaran = :id_tahun",
            [':id_semester' => $idSemester, ':id_tahun' => $idTahunAjaran]
        );
        return $semester !== null
            && $this->semesterBisaDisiapkan($idTahunAjaran, $semester['jenis_semester'], false);
    }

    public function ambilDaftarWali(int $idTahunAjaran): array {
        return $this->fetchAll(
            "SELECT k.id AS id_kelas, k.nama_kelas, pwk.id_guru, g.nama_lengkap AS nama_guru
             FROM kelas k
             LEFT JOIN penugasan_wali_kelas pwk
               ON pwk.id_kelas = k.id AND pwk.id_tahun_ajaran = :id_tahun AND pwk.apakah_aktif = 1
             LEFT JOIN guru g ON g.id = pwk.id_guru
             ORDER BY k.tingkat, k.nama_kelas",
            [':id_tahun' => $idTahunAjaran]
        );
    }

    public function simpanWali(int $idTahunAjaran, int $idKelas, int $idGuru): ?string {
        $this->db->beginTransaction();
        try {
            if (!$this->kunciTahunTidakAktif($idTahunAjaran)) {
                $this->db->rollBack();
                return 'Wali kelas hanya dapat disiapkan untuk tahun ajaran yang belum aktif.';
            }
            $guru = $this->fetchColumn(
                "SELECT id FROM guru WHERE id = :id_guru AND apakah_aktif = 1",
                [':id_guru' => $idGuru]
            );
            if ($guru === false) {
                $this->db->rollBack();
                return 'Guru wali kelas tidak ditemukan atau tidak aktif.';
            }
            $guruDiKelasLain = $this->fetchColumn(
                "SELECT id FROM penugasan_wali_kelas
                 WHERE id_tahun_ajaran = :id_tahun AND id_guru = :id_guru
                   AND apakah_aktif = 1 AND id_kelas <> :id_kelas
                 LIMIT 1",
                [':id_tahun' => $idTahunAjaran, ':id_guru' => $idGuru, ':id_kelas' => $idKelas]
            );
            if ($guruDiKelasLain !== false) {
                $this->db->rollBack();
                return 'Guru tersebut sudah ditugaskan sebagai wali kelas pada kelas lain di tahun ajaran ini.';
            }
            $kelasAda = $this->fetchColumn("SELECT id FROM kelas WHERE id = :id", [':id' => $idKelas]);
            if ($kelasAda === false) {
                $this->db->rollBack();
                return 'Kelas tidak ditemukan.';
            }
            $this->query(
                "INSERT INTO penugasan_wali_kelas
                    (id_guru, id_kelas, id_tahun_ajaran, apakah_aktif, tanggal_mulai_tugas)
                 VALUES (:id_guru, :id_kelas, :id_tahun, 1, CURDATE())
                 ON DUPLICATE KEY UPDATE id_guru = VALUES(id_guru), apakah_aktif = 1",
                [':id_guru' => $idGuru, ':id_kelas' => $idKelas, ':id_tahun' => $idTahunAjaran]
            );
            $this->db->commit();
            return null;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function simpanPenugasan(int $idTahunAjaran, int $idSemester, int $idKelas, int $idMapel, int $idGuru): ?string {
        $this->db->beginTransaction();
        try {
            $semester = $this->kunciSemesterBelumAktif($idTahunAjaran, $idSemester);
            if (!$semester) {
                $this->db->rollBack();
                return 'Penugasan hanya dapat disiapkan pada semester yang belum aktif.';
            }
            $kelas = $this->fetchOne("SELECT id_jurusan, tingkat FROM kelas WHERE id = :id", [':id' => $idKelas]);
            $template = $this->fetchOne(
                "SELECT id FROM kurikulum_mapel
                 WHERE id_tahun_ajaran = :id_tahun AND id_jurusan = :id_jurusan
                   AND tingkat = :tingkat AND jenis_semester = :jenis AND id_mapel = :id_mapel",
                [
                    ':id_tahun' => $idTahunAjaran,
                    ':id_jurusan' => (int)($kelas['id_jurusan'] ?? 0),
                    ':tingkat' => (int)($kelas['tingkat'] ?? 0),
                    ':jenis' => $semester['jenis_semester'],
                    ':id_mapel' => $idMapel
                ]
            );
            $mapel = $this->fetchOne(
                "SELECT id FROM mata_pelajaran
                 WHERE id = :id_mapel AND (id_jurusan IS NULL OR id_jurusan = :id_jurusan)",
                [':id_mapel' => $idMapel, ':id_jurusan' => (int)($kelas['id_jurusan'] ?? 0)]
            );
            $guruAda = $this->fetchColumn("SELECT id FROM guru WHERE id = :id AND apakah_aktif = 1", [':id' => $idGuru]);
            if (!$kelas || !$template || !$mapel || $guruAda === false) {
                $this->db->rollBack();
                return 'Kelas, kurikulum mapel, atau guru tidak valid untuk semester ini.';
            }
            $existing = $this->fetchAll(
                "SELECT id, id_guru
                 FROM penugasan_guru
                 WHERE id_tahun_ajaran = :id_tahun AND id_semester = :id_semester
                   AND id_kelas = :id_kelas AND id_mapel = :id_mapel",
                [
                    ':id_tahun' => $idTahunAjaran,
                    ':id_semester' => $idSemester,
                    ':id_kelas' => $idKelas,
                    ':id_mapel' => $idMapel
                ]
            );
            if (count($existing) > 1) {
                $this->db->rollBack();
                return 'Ada lebih dari satu penugasan untuk mapel ini. Rapikan data duplikat sebelum melanjutkan.';
            }
            if ($existing) {
                if ((int)$existing[0]['id_guru'] !== $idGuru) {
                    $adaJadwal = $this->fetchColumn(
                        "SELECT id FROM jadwal_pelajaran WHERE id_penugasan_guru = :id LIMIT 1",
                        [':id' => (int)$existing[0]['id']]
                    );
                    if ($adaJadwal !== false) {
                        $this->db->rollBack();
                        return 'Hapus jadwal lama terlebih dahulu sebelum mengganti guru pengajar.';
                    }
                    $this->query("UPDATE penugasan_guru SET id_guru = :id_guru WHERE id = :id", [
                        ':id_guru' => $idGuru,
                        ':id' => (int)$existing[0]['id']
                    ]);
                }
            } else {
                $this->query(
                    "INSERT INTO penugasan_guru
                        (id_tahun_ajaran, id_semester, id_guru, id_mapel, id_kelas)
                     VALUES (:id_tahun, :id_semester, :id_guru, :id_mapel, :id_kelas)",
                    [
                        ':id_tahun' => $idTahunAjaran,
                        ':id_semester' => $idSemester,
                        ':id_guru' => $idGuru,
                        ':id_mapel' => $idMapel,
                        ':id_kelas' => $idKelas
                    ]
                );
            }
            $this->db->commit();
            return null;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function hapusPenugasanPersiapan(int $idTahunAjaran, int $idSemester, int $idKelas, int $idMapel): ?string {
        $this->db->beginTransaction();
        try {
            if (!$this->kunciSemesterBelumAktif($idTahunAjaran, $idSemester)) {
                $this->db->rollBack();
                return 'Penugasan hanya dapat dihapus pada semester persiapan yang belum aktif.';
            }
            $jumlahNilai = (int)$this->fetchColumn(
                "SELECT COUNT(*)
                 FROM nilai_siswa ns
                 JOIN penugasan_guru pg ON pg.id = ns.id_penugasan_guru
                 WHERE pg.id_tahun_ajaran = :id_tahun
                   AND pg.id_semester = :id_semester
                   AND pg.id_kelas = :id_kelas
                   AND pg.id_mapel = :id_mapel",
                [
                    ':id_tahun' => $idTahunAjaran,
                    ':id_semester' => $idSemester,
                    ':id_kelas' => $idKelas,
                    ':id_mapel' => $idMapel
                ]
            );
            if ($jumlahNilai > 0) {
                $this->db->rollBack();
                return 'Penugasan tidak dapat dihapus karena sudah memiliki data nilai.';
            }
            $jumlahDihapus = $this->query(
                "DELETE FROM penugasan_guru
                 WHERE id_tahun_ajaran = :id_tahun
                   AND id_semester = :id_semester
                   AND id_kelas = :id_kelas
                   AND id_mapel = :id_mapel",
                [
                    ':id_tahun' => $idTahunAjaran,
                    ':id_semester' => $idSemester,
                    ':id_kelas' => $idKelas,
                    ':id_mapel' => $idMapel
                ]
            )->rowCount();
            if ($jumlahDihapus === 0) {
                $this->db->rollBack();
                return 'Penugasan mapel tidak ditemukan.';
            }
            $this->db->commit();
            return null;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function ambilDaftarGuru(): array {
        return $this->fetchAll(
            "SELECT id, nama_lengkap FROM guru WHERE apakah_aktif = 1 ORDER BY nama_lengkap"
        );
    }

    public function pratinjauKenaikan(int $idTahunBaru): array {
        $periodeAktif = $this->ambilPeriodeAktif();
        if (!$periodeAktif || $periodeAktif['jenis_semester'] !== 'genap') {
            return ['siap' => false, 'naik' => 0, 'lulus' => 0, 'message' => 'Semester Genap tahun berjalan belum aktif.'];
        }
        $idTahunLama = (int)$periodeAktif['id_tahun_ajaran'];
        $tahunBaru = $this->fetchOne(
            "SELECT id, apakah_aktif, tanggal_mulai
             FROM tahun_ajaran WHERE id = :id",
            [':id' => $idTahunBaru]
        );
        if (!$tahunBaru || (int)$tahunBaru['apakah_aktif'] === 1
            || $tahunBaru['tanggal_mulai'] <= $periodeAktif['tahun_selesai']
            || (int)substr($tahunBaru['tanggal_mulai'], 0, 4) !== (int)substr($periodeAktif['tahun_mulai'], 0, 4) + 1
        ) {
            return ['siap' => false, 'naik' => 0, 'lulus' => 0, 'message' => 'Tahun tujuan harus dimulai setelah tahun aktif berakhir dan berada pada tahun kalender berikutnya.'];
        }
        $naik = (int)$this->fetchColumn(
            "SELECT COUNT(*)
             FROM riwayat_kelas_siswa rks
             JOIN siswa s ON s.id = rks.id_siswa
             JOIN kelas k ON k.id = rks.id_kelas
             WHERE rks.id_tahun_ajaran = :id_tahun AND rks.status_penempatan = 'aktif'
               AND s.status_siswa = 'aktif' AND k.tingkat IN (10, 11)",
            [':id_tahun' => $idTahunLama]
        );
        $lulus = (int)$this->fetchColumn(
            "SELECT COUNT(*)
             FROM riwayat_kelas_siswa rks
             JOIN siswa s ON s.id = rks.id_siswa
             JOIN kelas k ON k.id = rks.id_kelas
             WHERE rks.id_tahun_ajaran = :id_tahun AND rks.status_penempatan = 'aktif'
               AND s.status_siswa = 'aktif' AND k.tingkat = 12",
            [':id_tahun' => $idTahunLama]
        );
        return ['siap' => true, 'naik' => $naik, 'lulus' => $lulus, 'message' => null];
    }

    public function hapusJadwalPersiapan(int $idTahunAjaran, int $idSemester, int $idJadwal): ?string {
        $this->db->beginTransaction();
        try {
            if (!$this->kunciSemesterBelumAktif($idTahunAjaran, $idSemester)) {
                $this->db->rollBack();
                return 'Jadwal hanya dapat diubah pada semester yang belum aktif.';
            }
            $stmt = $this->query(
                "DELETE jp
                 FROM jadwal_pelajaran jp
                 JOIN penugasan_guru pg ON pg.id = jp.id_penugasan_guru
                 WHERE jp.id = :id_jadwal
                   AND pg.id_tahun_ajaran = :id_tahun
                   AND pg.id_semester = :id_semester",
                [':id_jadwal' => $idJadwal, ':id_tahun' => $idTahunAjaran, ':id_semester' => $idSemester]
            );
            if ($stmt->rowCount() !== 1) {
                $this->db->rollBack();
                return 'Jadwal tidak ditemukan pada semester yang dipilih.';
            }
            $this->db->commit();
            return null;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function evaluasiKesiapanSemester(int $idSemester): array {
        $semester = $this->fetchOne(
            "SELECT sem.id, sem.id_tahun_ajaran, sem.jenis_semester, sem.apakah_aktif,
                    ta.nama_tahun, ta.apakah_aktif AS tahun_aktif
             FROM semester sem JOIN tahun_ajaran ta ON ta.id = sem.id_tahun_ajaran
             WHERE sem.id = :id",
            [':id' => $idSemester]
        );
        if (!$semester) {
            return ['siap' => false, 'errors' => ['Semester tidak ditemukan.']];
        }

        $errors = [];
        $idTahun = (int)$semester['id_tahun_ajaran'];
        $kelasList = $this->fetchAll("SELECT id, id_jurusan, tingkat, nama_kelas FROM kelas ORDER BY tingkat, nama_kelas");
        foreach ($kelasList as $kelas) {
            $templates = $this->fetchAll(
                "SELECT km.id_mapel, km.pertemuan_per_minggu, mp.nama_mapel
                 FROM kurikulum_mapel km
                 JOIN mata_pelajaran mp ON mp.id = km.id_mapel
                 WHERE km.id_tahun_ajaran = :id_tahun
                   AND km.id_jurusan = :id_jurusan
                   AND km.tingkat = :tingkat
                   AND km.jenis_semester = :jenis",
                [
                    ':id_tahun' => $idTahun,
                    ':id_jurusan' => (int)$kelas['id_jurusan'],
                    ':tingkat' => (int)$kelas['tingkat'],
                    ':jenis' => $semester['jenis_semester']
                ]
            );
            if (!$templates) {
                $errors[] = "{$kelas['nama_kelas']}: template kurikulum belum diisi.";
                continue;
            }

            $waliCount = (int)$this->fetchColumn(
                "SELECT COUNT(*)
                 FROM penugasan_wali_kelas pwk
                 JOIN guru g ON g.id = pwk.id_guru
                 WHERE pwk.id_tahun_ajaran = :id_tahun AND pwk.id_kelas = :id_kelas
                   AND pwk.apakah_aktif = 1 AND g.apakah_aktif = 1",
                [':id_tahun' => $idTahun, ':id_kelas' => (int)$kelas['id']]
            );
            if ($waliCount !== 1) {
                $errors[] = "{$kelas['nama_kelas']}: wali kelas belum ditetapkan.";
            }

            foreach ($templates as $template) {
                $assignment = $this->fetchOne(
                    "SELECT pg.id, g.apakah_aktif AS guru_aktif, COUNT(DISTINCT jp.id) AS jumlah_jadwal
                     FROM penugasan_guru pg
                     JOIN guru g ON g.id = pg.id_guru
                     LEFT JOIN jadwal_pelajaran jp ON jp.id_penugasan_guru = pg.id
                     WHERE pg.id_tahun_ajaran = :id_tahun
                       AND pg.id_semester = :id_semester
                       AND pg.id_kelas = :id_kelas
                       AND pg.id_mapel = :id_mapel
                     GROUP BY pg.id
                     LIMIT 2",
                    [
                        ':id_tahun' => $idTahun,
                        ':id_semester' => $idSemester,
                        ':id_kelas' => (int)$kelas['id'],
                        ':id_mapel' => (int)$template['id_mapel']
                    ]
                );
                $assignmentCount = (int)$this->fetchColumn(
                    "SELECT COUNT(*) FROM penugasan_guru
                     WHERE id_tahun_ajaran = :id_tahun AND id_semester = :id_semester
                       AND id_kelas = :id_kelas AND id_mapel = :id_mapel",
                    [
                        ':id_tahun' => $idTahun,
                        ':id_semester' => $idSemester,
                        ':id_kelas' => (int)$kelas['id'],
                        ':id_mapel' => (int)$template['id_mapel']
                    ]
                );
                if ($assignmentCount !== 1) {
                    $errors[] = "{$kelas['nama_kelas']} - {$template['nama_mapel']}: penugasan guru belum tepat satu.";
                    continue;
                }
                if ((int)($assignment['guru_aktif'] ?? 0) !== 1) {
                    $errors[] = "{$kelas['nama_kelas']} - {$template['nama_mapel']}: guru pengajar tidak aktif.";
                }
                if ((int)($assignment['jumlah_jadwal'] ?? 0) !== (int)$template['pertemuan_per_minggu']) {
                    $errors[] = "{$kelas['nama_kelas']} - {$template['nama_mapel']}: jadwal harus berjumlah {$template['pertemuan_per_minggu']} pertemuan per minggu.";
                }
            }
        }

        $extraAssignmentCount = (int)$this->fetchColumn(
            "SELECT COUNT(*)
             FROM penugasan_guru pg
             JOIN kelas k ON k.id = pg.id_kelas
             WHERE pg.id_tahun_ajaran = :id_tahun AND pg.id_semester = :id_semester
               AND NOT EXISTS (
                   SELECT 1 FROM kurikulum_mapel km
                   WHERE km.id_tahun_ajaran = pg.id_tahun_ajaran
                     AND km.id_jurusan = k.id_jurusan
                     AND km.tingkat = k.tingkat
                     AND km.jenis_semester = :jenis
                     AND km.id_mapel = pg.id_mapel
               )",
            [
                ':id_tahun' => $idTahun,
                ':id_semester' => $idSemester,
                ':jenis' => $semester['jenis_semester']
            ]
        );
        if ($extraAssignmentCount > 0) {
            $errors[] = "{$extraAssignmentCount} penugasan guru tidak tercantum pada template kurikulum.";
        }

        $waliDuplikat = $this->fetchOne(
            "SELECT g.nama_lengkap, COUNT(DISTINCT pwk.id_kelas) AS jumlah_kelas
             FROM penugasan_wali_kelas pwk
             JOIN guru g ON g.id = pwk.id_guru
             WHERE pwk.id_tahun_ajaran = :id_tahun AND pwk.apakah_aktif = 1
             GROUP BY pwk.id_guru HAVING COUNT(DISTINCT pwk.id_kelas) > 1
             LIMIT 1",
            [':id_tahun' => $idTahun]
        );
        if ($waliDuplikat) {
            $errors[] = "Guru {$waliDuplikat['nama_lengkap']} ditugaskan sebagai wali di lebih dari satu kelas.";
        }

        $bentrok = $this->fetchOne(
            "SELECT jp1.hari, jp1.jam_mulai, jp1.ruangan,
                    k1.nama_kelas AS kelas_pertama, k2.nama_kelas AS kelas_kedua
             FROM jadwal_pelajaran jp1
             JOIN penugasan_guru pg1 ON pg1.id = jp1.id_penugasan_guru
             JOIN kelas k1 ON k1.id = pg1.id_kelas
             JOIN jadwal_pelajaran jp2 ON jp2.id > jp1.id
               AND jp2.hari = jp1.hari
               AND jp2.jam_mulai < jp1.jam_selesai
               AND jp2.jam_selesai > jp1.jam_mulai
             JOIN penugasan_guru pg2 ON pg2.id = jp2.id_penugasan_guru
             JOIN kelas k2 ON k2.id = pg2.id_kelas
             WHERE pg1.id_tahun_ajaran = :id_tahun_1 AND pg1.id_semester = :id_semester_1
               AND pg2.id_tahun_ajaran = :id_tahun_2 AND pg2.id_semester = :id_semester_2
               AND (pg1.id_guru = pg2.id_guru OR pg1.id_kelas = pg2.id_kelas OR jp1.ruangan = jp2.ruangan)
             LIMIT 1",
            [
                ':id_tahun_1' => $idTahun,
                ':id_semester_1' => $idSemester,
                ':id_tahun_2' => $idTahun,
                ':id_semester_2' => $idSemester
            ]
        );
        if ($bentrok) {
            $errors[] = "Jadwal bentrok pada hari {$bentrok['hari']} pukul {$bentrok['jam_mulai']} di {$bentrok['ruangan']}.";
        }

        return ['siap' => $errors === [], 'errors' => $errors, 'semester' => $semester];
    }

    public function aktifkanSemesterGenap(int $idTahunAjaran): ?string {
        $this->db->beginTransaction();
        try {
            $tahun = $this->fetchOne(
                "SELECT id, apakah_aktif FROM tahun_ajaran WHERE id = :id FOR UPDATE",
                [':id' => $idTahunAjaran]
            );
            if (!$tahun || (int)$tahun['apakah_aktif'] !== 1) {
                $this->db->rollBack();
                return 'Tahun ajaran yang dipilih bukan tahun ajaran aktif.';
            }
            $tahunAktifTerkunci = $this->query(
                "SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 ORDER BY id FOR UPDATE"
            )->fetchAll();
            $semesterAktifTerkunci = $this->query(
                "SELECT id FROM semester WHERE apakah_aktif = 1 ORDER BY id FOR UPDATE"
            )->fetchAll();
            if (count($tahunAktifTerkunci) !== 1 || count($semesterAktifTerkunci) !== 1) {
                $this->db->rollBack();
                return 'Status periode tidak konsisten: harus ada tepat satu tahun ajaran dan satu semester aktif.';
            }
            $periodeAktif = $this->ambilPeriodeAktif();
            if (!$periodeAktif || (int)$periodeAktif['id_tahun_ajaran'] !== $idTahunAjaran
                || $periodeAktif['jenis_semester'] !== 'ganjil'
            ) {
                $this->db->rollBack();
                return 'Pergantian semester hanya dapat dilakukan dari Ganjil ke Genap pada tahun yang sama.';
            }
            $semesterGenap = $this->fetchOne(
                "SELECT id FROM semester WHERE id_tahun_ajaran = :id_tahun AND jenis_semester = 'genap' FOR UPDATE",
                [':id_tahun' => $idTahunAjaran]
            );
            if (!$semesterGenap) {
                $this->db->rollBack();
                return 'Semester Genap belum dibuat untuk tahun ajaran aktif.';
            }
            $kesiapan = $this->evaluasiKesiapanSemester((int)$semesterGenap['id']);
            if (!$kesiapan['siap']) {
                $this->db->rollBack();
                return $this->pesanKesiapan($kesiapan['errors']);
            }

            $this->query("UPDATE semester SET apakah_aktif = 0 WHERE apakah_aktif = 1");
            $this->query("UPDATE semester SET apakah_aktif = 1 WHERE id = :id", [':id' => (int)$semesterGenap['id']]);
            $this->db->commit();
            return null;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function finalisasiKenaikan(int $idTahunBaru): array {
        $this->db->beginTransaction();
        try {
            $periodeAktif = $this->ambilPeriodeAktif();
            if (!$periodeAktif || $periodeAktif['jenis_semester'] !== 'genap') {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Kenaikan kelas tahunan hanya dapat dilakukan setelah Semester Genap aktif.'];
            }
            $idTahunLama = (int)$periodeAktif['id_tahun_ajaran'];
            $this->query(
                "SELECT id FROM tahun_ajaran WHERE id IN (:id_lama, :id_baru) ORDER BY id FOR UPDATE",
                [':id_lama' => $idTahunLama, ':id_baru' => $idTahunBaru]
            )->fetchAll();
            $tahunAktifTerkunci = $this->query(
                "SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 ORDER BY id FOR UPDATE"
            )->fetchAll();
            $semesterAktifTerkunci = $this->query(
                "SELECT id FROM semester WHERE apakah_aktif = 1 ORDER BY id FOR UPDATE"
            )->fetchAll();
            if (count($tahunAktifTerkunci) !== 1 || count($semesterAktifTerkunci) !== 1) {
                $this->db->rollBack();
                return [
                    'ok' => false,
                    'message' => 'Status periode tidak konsisten: harus ada tepat satu tahun ajaran dan satu semester aktif.'
                ];
            }
            $periodeTerkunci = $this->ambilPeriodeAktifTerkunci();
            if (!$periodeTerkunci || (int)$periodeTerkunci['id_tahun_ajaran'] !== $idTahunLama
                || $periodeTerkunci['jenis_semester'] !== 'genap'
            ) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Periode aktif berubah saat proses dimulai. Muat ulang halaman dan coba kembali.'];
            }
            $periodeAktif = $periodeTerkunci;

            $tahunBaru = $this->fetchOne(
                "SELECT id, apakah_aktif, tanggal_mulai
                 FROM tahun_ajaran WHERE id = :id FOR UPDATE",
                [':id' => $idTahunBaru]
            );
            if (!$tahunBaru || (int)$tahunBaru['apakah_aktif'] === 1 || $idTahunBaru === $idTahunLama) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Pilih tahun ajaran baru yang masih nonaktif.'];
            }
            if ($tahunBaru['tanggal_mulai'] <= $periodeAktif['tahun_selesai']
                || (int)substr($tahunBaru['tanggal_mulai'], 0, 4) !== (int)substr($periodeAktif['tahun_mulai'], 0, 4) + 1
            ) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Tahun tujuan harus dimulai setelah tahun aktif berakhir dan berada pada tahun kalender berikutnya.'];
            }
            $semesterBaru = $this->fetchAll(
                "SELECT id, jenis_semester FROM semester
                 WHERE id_tahun_ajaran = :id_tahun ORDER BY jenis_semester FOR UPDATE",
                [':id_tahun' => $idTahunBaru]
            );
            $idGanjilBaru = null;
            $idGenapBaru = null;
            foreach ($semesterBaru as $semester) {
                if ($semester['jenis_semester'] === 'ganjil') $idGanjilBaru = (int)$semester['id'];
                if ($semester['jenis_semester'] === 'genap') $idGenapBaru = (int)$semester['id'];
            }
            if ($idGanjilBaru === null || $idGenapBaru === null) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Tahun baru harus memiliki Semester Ganjil dan Genap.'];
            }
            foreach ([$idGanjilBaru, $idGenapBaru] as $idSemester) {
                $kesiapan = $this->evaluasiKesiapanSemester($idSemester);
                if (!$kesiapan['siap']) {
                    $this->db->rollBack();
                    return ['ok' => false, 'message' => $this->pesanKesiapan($kesiapan['errors'])];
                }
            }

            $penempatanAktifTanpaKelas = (int)$this->fetchColumn(
                "SELECT COUNT(*)
                 FROM siswa s
                 WHERE s.status_siswa = 'aktif'
                   AND NOT EXISTS (
                       SELECT 1 FROM riwayat_kelas_siswa rks
                       WHERE rks.id_siswa = s.id
                         AND rks.id_tahun_ajaran = :id_tahun
                         AND rks.status_penempatan = 'aktif'
                   )",
                [':id_tahun' => $idTahunLama]
            );
            if ($penempatanAktifTanpaKelas > 0) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => "{$penempatanAktifTanpaKelas} siswa aktif belum memiliki penempatan kelas pada tahun ajaran aktif."];
            }
            $tingkatAktifTidakDidukung = (int)$this->fetchColumn(
                "SELECT COUNT(*)
                 FROM riwayat_kelas_siswa rks
                 JOIN siswa s ON s.id = rks.id_siswa
                 JOIN kelas k ON k.id = rks.id_kelas
                 WHERE rks.id_tahun_ajaran = :id_tahun
                   AND rks.status_penempatan = 'aktif'
                   AND s.status_siswa = 'aktif'
                   AND k.tingkat NOT IN (10, 11, 12)",
                [':id_tahun' => $idTahunLama]
            );
            if ($tingkatAktifTidakDidukung > 0) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => "{$tingkatAktifTidakDidukung} siswa berada di tingkat yang tidak dapat diproses otomatis."];
            }
            $penempatanTujuanSudahAda = $this->fetchColumn(
                "SELECT rks.id
                 FROM riwayat_kelas_siswa rks
                 JOIN siswa s ON s.id = rks.id_siswa
                 WHERE rks.id_tahun_ajaran = :id_tahun AND s.status_siswa = 'aktif'
                 LIMIT 1",
                [':id_tahun' => $idTahunBaru]
            );
            if ($penempatanTujuanSudahAda !== false) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Tahun tujuan sudah memiliki penempatan siswa; finalisasi dibatalkan agar data tidak terduplikasi.'];
            }

            $kelasTanpaTujuan = (int)$this->fetchColumn(
                "SELECT COUNT(*)
                 FROM kelas sumber
                 WHERE sumber.tingkat IN (10, 11)
                   AND NOT EXISTS (
                       SELECT 1 FROM kelas tujuan
                       WHERE tujuan.id_jurusan = sumber.id_jurusan
                         AND tujuan.tingkat = sumber.tingkat + 1
                         AND tujuan.nomor_rombel = sumber.nomor_rombel
                   )"
            );
            if ($kelasTanpaTujuan > 0) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => "{$kelasTanpaTujuan} kelas belum memiliki pasangan naik tingkat dengan nomor rombel yang sama."];
            }

            $jumlahSiswaNaik = (int)$this->fetchColumn(
                "SELECT COUNT(*)
                 FROM riwayat_kelas_siswa rks
                 JOIN siswa s ON s.id = rks.id_siswa
                 JOIN kelas k ON k.id = rks.id_kelas
                 WHERE rks.id_tahun_ajaran = :id_tahun AND rks.status_penempatan = 'aktif'
                   AND s.status_siswa = 'aktif' AND k.tingkat IN (10, 11)",
                [':id_tahun' => $idTahunLama]
            );
            $jumlahLulus = (int)$this->fetchColumn(
                "SELECT COUNT(*)
                 FROM riwayat_kelas_siswa rks
                 JOIN siswa s ON s.id = rks.id_siswa
                 JOIN kelas k ON k.id = rks.id_kelas
                 WHERE rks.id_tahun_ajaran = :id_tahun AND rks.status_penempatan = 'aktif'
                   AND s.status_siswa = 'aktif' AND k.tingkat = 12",
                [':id_tahun' => $idTahunLama]
            );

            $this->query(
                "UPDATE riwayat_kelas_siswa rks
                 JOIN siswa s ON s.id = rks.id_siswa
                 JOIN kelas k ON k.id = rks.id_kelas
                 SET rks.status_penempatan = CASE WHEN k.tingkat = 12 THEN 'lulus' ELSE 'naik' END
                 WHERE rks.id_tahun_ajaran = :id_tahun
                   AND rks.status_penempatan = 'aktif'
                   AND s.status_siswa = 'aktif'
                   AND k.tingkat IN (10, 11, 12)",
                [':id_tahun' => $idTahunLama]
            );
            $this->query(
                "INSERT INTO riwayat_kelas_siswa (id_siswa, id_kelas, id_tahun_ajaran, status_penempatan)
                 SELECT rks.id_siswa, tujuan.id, :id_tahun_baru, 'aktif'
                 FROM riwayat_kelas_siswa rks
                 JOIN siswa s ON s.id = rks.id_siswa
                 JOIN kelas asal ON asal.id = rks.id_kelas
                 JOIN kelas tujuan
                   ON tujuan.id_jurusan = asal.id_jurusan
                  AND tujuan.tingkat = asal.tingkat + 1
                  AND tujuan.nomor_rombel = asal.nomor_rombel
                 WHERE rks.id_tahun_ajaran = :id_tahun_lama
                   AND rks.status_penempatan = 'naik'
                   AND s.status_siswa = 'aktif'",
                [':id_tahun_baru' => $idTahunBaru, ':id_tahun_lama' => $idTahunLama]
            );
            $jumlahDisimpan = (int)$this->db->query("SELECT ROW_COUNT()")->fetchColumn();
            if ($jumlahDisimpan !== $jumlahSiswaNaik) {
                $this->db->rollBack();
                return [
                    'ok' => false,
                    'message' => 'Jumlah penempatan siswa baru tidak sesuai dengan jumlah siswa yang seharusnya naik kelas. Tidak ada perubahan yang disimpan.'
                ];
            }
            $this->query(
                "UPDATE siswa s
                 JOIN riwayat_kelas_siswa rks ON rks.id_siswa = s.id AND rks.id_tahun_ajaran = :id_tahun
                 JOIN kelas k ON k.id = rks.id_kelas
                 SET s.status_siswa = 'lulus'
                 WHERE rks.status_penempatan = 'lulus' AND k.tingkat = 12 AND s.status_siswa = 'aktif'",
                [':id_tahun' => $idTahunLama]
            );
            $this->query("UPDATE penugasan_wali_kelas SET apakah_aktif = 0 WHERE id_tahun_ajaran = :id_tahun", [':id_tahun' => $idTahunLama]);
            $this->query("UPDATE tahun_ajaran SET apakah_aktif = 0");
            $this->query("UPDATE semester SET apakah_aktif = 0");
            $this->query("UPDATE tahun_ajaran SET apakah_aktif = 1 WHERE id = :id", [':id' => $idTahunBaru]);
            $this->query("UPDATE semester SET apakah_aktif = 1 WHERE id = :id", [':id' => $idGanjilBaru]);

            $this->db->commit();
            return [
                'ok' => true,
                'message' => "{$jumlahSiswaNaik} siswa naik kelas dan {$jumlahLulus} siswa kelas 12 lulus."
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function semesterBisaDisiapkan(int $idTahunAjaran, string $jenisSemester, bool $lock): bool {
        if ($lock) {
            $this->fetchOne("SELECT id FROM tahun_ajaran WHERE id = :id FOR UPDATE", [':id' => $idTahunAjaran]);
        }
        $semester = $this->fetchOne(
            "SELECT sem.id
             FROM semester sem
             JOIN tahun_ajaran ta ON ta.id = sem.id_tahun_ajaran
             WHERE sem.id_tahun_ajaran = :id_tahun
               AND sem.jenis_semester = :jenis
               AND sem.apakah_aktif = 0
               AND (
                   (ta.apakah_aktif = 0 AND ta.tanggal_mulai > (
                       SELECT tanggal_selesai FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1
                   ))
                   OR (
                       ta.apakah_aktif = 1
                       AND sem.jenis_semester = 'genap'
                       AND EXISTS (
                           SELECT 1 FROM semester aktif
                           WHERE aktif.id_tahun_ajaran = ta.id
                             AND aktif.jenis_semester = 'ganjil'
                             AND aktif.apakah_aktif = 1
                       )
                   )
               )" . ($lock ? " FOR UPDATE" : ""),
            [':id_tahun' => $idTahunAjaran, ':jenis' => $jenisSemester]
        );
        return $semester !== null;
    }

    private function kunciTahunTidakAktif(int $idTahunAjaran): bool {
        $tahun = $this->fetchOne(
            "SELECT id, apakah_aktif
             FROM tahun_ajaran
             WHERE id = :id
               AND apakah_aktif = 0
               AND tanggal_mulai > (
                   SELECT tanggal_selesai FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1
               )
             FOR UPDATE",
            [':id' => $idTahunAjaran]
        );
        return $tahun !== null;
    }

    private function kunciSemesterBelumAktif(int $idTahunAjaran, int $idSemester): ?array {
        $this->fetchOne("SELECT id FROM tahun_ajaran WHERE id = :id FOR UPDATE", [':id' => $idTahunAjaran]);
        return $this->fetchOne(
            "SELECT sem.id, sem.jenis_semester
             FROM semester sem
             JOIN tahun_ajaran ta ON ta.id = sem.id_tahun_ajaran
             WHERE sem.id = :id_semester
               AND sem.id_tahun_ajaran = :id_tahun
               AND sem.apakah_aktif = 0
               AND (
                   (ta.apakah_aktif = 0 AND ta.tanggal_mulai > (
                       SELECT tanggal_selesai FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1
                   ))
                   OR (
                       ta.apakah_aktif = 1
                       AND sem.jenis_semester = 'genap'
                       AND EXISTS (
                           SELECT 1 FROM semester aktif
                           WHERE aktif.id_tahun_ajaran = ta.id
                             AND aktif.jenis_semester = 'ganjil'
                             AND aktif.apakah_aktif = 1
                       )
                   )
               )
             FOR UPDATE",
            [':id_semester' => $idSemester, ':id_tahun' => $idTahunAjaran]
        );
    }

    private function tanggalValid(string $tanggal): DateTimeImmutable {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $tanggal);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m-d') !== $tanggal
        ) {
            throw new InvalidArgumentException('Format tanggal harus YYYY-MM-DD dan tanggal harus valid.');
        }
        return $date;
    }

    private function tanggalInput(array $data, string $key): string {
        return is_string($data[$key] ?? null) ? $data[$key] : '';
    }

    private function pesanKesiapan(array $errors): string {
        $tampil = array_slice($errors, 0, 5);
        $tambahan = count($errors) > count($tampil) ? ' dan ' . (count($errors) - count($tampil)) . ' masalah lainnya' : '';
        return 'Setup belum siap: ' . implode(' | ', $tampil) . $tambahan;
    }
}
