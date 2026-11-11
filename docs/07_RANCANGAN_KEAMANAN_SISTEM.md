# CETAK BIRU RANCANGAN KEAMANAN SISTEM INFORMASI AKADEMIK
## SISTEM INFORMASI AKADEMIK (SIAKAD) SMK NEGERI 9 TANGERANG
**Kode Proyek:** `web-komet` | **Database:** `db_smkn9` | **Klasifikasi:** Dokumen Teknis Keamanan (Security Blueprint)

---

## 1. Pemisahan Frontend, Backend, dan CSS (Clean Architecture & Separation of Concerns)

Untuk mempermudah pemeliharaan (*maintenance*), skalabilitas, serta mencegah celah keamanan kode campur aduk (*spaghetti code*), proyek `web-komet` menerapkan pemisahan ketat antara 3 lapisan:

```
web-komet/
├── app/                      # [BACKEND LAYER]
│   ├── Controllers/          # Logika pemrosesan HTTP request, otorisasi, dan delegasi service
│   ├── Middleware/           # Lapisan pertahanan (Auth, Role, CSRF, Anti-IDOR, RateLimit)
│   ├── Models/               # Interaksi basis data PDO & query berparameter
│   ├── Services/             # Logika bisnis inti (Deteksi bentrok jadwal, kalkulasi nilai)
│   └── Helpers/              # Fungsi enkripsi, validasi berkas, dan sanitasi output
│
├── views/                    # [FRONTEND LAYER - VIEW TEMPLATES]
│   ├── layouts/              # Kerangka antarmuka (header, sidebar, navbar, footer, mobile-nav)
│   ├── components/           # Komponen reusable (modal konfirmasi, pagination, alert toast)
│   └── [modul]/              # Template murni presentasi HTML + semantic PHP echo
│                             # DILARANG KERAS ada inline CSS atau query SQL di dalam view!
│
└── public/                   # [WEB SERVER DOCUMENT ROOT & STATIC ASSETS]
    ├── index.php             # Front Controller (Satu-satunya gerbang akses publik)
    ├── assets/
    │   ├── css/              # [CSS LAYER TERPISAH]
    │   │   ├── style.css     # Variabel warna resmi (#112E81, #4647AE, #4382DF, #AACCD6)
    │   │   ├── components.css# Styling tabel, modal, form input, dan badge status
    │   │   └── responsive.css# Breakpoints khusus desktop, tablet, dan smartphone Android/iOS
    │   ├── js/               # Skrip interaksi UI, AJAX token CSRF, dan image preview
    │   └── images/           # Aset statis institusi (logosmkn9.png)
    └── uploads/              # Berkas unggahan dinamis (Poster event OSIS, Pasfoto)
```

### Keunggulan Pemisahan Ini:
1. **Keamanan Maksimal:** Seluruh kode aplikasi (`app/`, `views/`, `config/`) berada di luar direktori web yang dapat diakses langsung oleh URL browser.
2. **Keterbacaan & Maintenance:** Perancang antarmuka (UI Designer) dapat memodifikasi berkas CSS di `public/assets/css/` tanpa risiko merusak logika backend PHP.
3. **Pemberantasan Inline CSS:** Seluruh styling wajib menggunakan *class selectors* terdefinisi di file CSS eksternal.

---

## 2. Rincian 14 Pilar Spesifikasi Keamanan Sistem

---

### 2.1. Password Hashing Menggunakan Mekanisme yang Aman
- **Ancaman:** Pencurian basis data melalui celah SQL dump atau serangan *credential stuffing* menggunakan *rainbow tables*.
- **Mekanisme Proteksi:**
  - Menggunakan fungsi bawaan PHP `password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])` atau `PASSWORD_ARGON2ID`.
  - Garam (*salt*) kriptografis dihasilkan secara acak dan otomatis oleh mesin hashing.
  - Memeriksa kebutuhan pembaruan hash saat login menggunakan `password_needs_rehash()`.
  - **Dilarang Keras:** Penggunaan fungsi *hashing* usang seperti `md5()`, `sha1()`, atau menyimpan kata sandi dalam bentuk *plaintext*.

#### Kode Implementasi:
```php
class KeamananKataSandi {
    public static function hash(string $kataSandi): string {
        return password_hash($kataSandi, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifikasi(string $kataSandi, string $hashDb): bool {
        return password_verify($kataSandi, $hashDb);
    }
}
```

---

### 2.2. Manajemen Sesi yang Aman (Session Management)
- **Ancaman:** *Session Hijacking* (pembajakan sesi melalui pencurian cookie), *Session Fixation* (penyerang memaksakan ID sesi korban), dan *Cross-Site Scripting Cookie Theft*.
- **Mekanisme Proteksi:**
  1. **Konfigurasi Cookie Aman (`php.ini` / runtime):**
     - `session.cookie_httponly = 1`: Mencegah script JavaScript (XSS) membaca token sesi `PHPSESSID`.
     - `session.cookie_secure = 1`: Memastikan cookie hanya dikirim melalui koneksi terenkripsi HTTPS.
     - `session.cookie_samesite = 'Lax'`: Mencegah cookie sesi dikirim pada serangan CSRF lintas domain.
     - `session.use_strict_mode = 1`: Menolak ID sesi yang diinisialisasi oleh pengguna luar.
  2. **Regenerasi Session ID:** Menjalankan `session_regenerate_id(true)` tepat setelah pengguna berhasil login atau saat pergantian hak akses.
  3. **Batas Waktu Sesi (Timeout):**
     - *Idle Timeout:* Sesi otomatis hangus jika tidak ada aktivitas selama 120 menit.
     - *Absolute Timeout:* Sesi wajib login ulang setelah 8 jam demi kepatuhan keamanan.
  4. **Session Fingerprinting:** Menyimpan hash dari `User-Agent` dan subnet IP pengguna di sesi. Jika terjadi perpindahan browser tiba-tiba, sesi langsung dihancurkan (*session destroy*).

---

### 2.3. Role-Based Access Control (RBAC) di Sisi Server
- **Ancaman:** Pengguna dengan hak akses rendah (misal: Siswa atau OSIS) mencoba membuka URL halaman administrasi (misal: `/kesiswaan/nilai/kunci` atau `/kaprog/jadwal/edit`).
- **Mekanisme Proteksi:**
  - Pengecekan peran dilakukan di lapisan `Middleware` sebelum Controller manapun dieksekusi.
  - Hak akses dikunci berdasarkan kode peran terverifikasi pada sesi pengguna di database.

#### Kode Implementasi `RoleMiddleware.php`:
```php
class RoleMiddleware {
    public static function cekAkses(array $peranDiizinkan): void {
        if (!isset($_SESSION['pengguna_id'])) {
            header('Location: /login');
            exit;
        }

        $kodePeranPengguna = $_SESSION['kode_peran'] ?? '';

        if (!in_array($kodePeranPengguna, $peranDiizinkan, true)) {
            http_response_code(403);
            require_once __DIR__ . '/../../views/errors/403.php';
            exit;
        }
    }
}
```

---

### 2.4. Pembatasan Akses Berdasarkan Jurusan dan Kelas di Sisi Server (Anti-IDOR)
- **Ancaman Nyata (Insecure Direct Object References - IDOR):**
  - **Skenario 1 (Kaprog):** Kaprog DKV mencoba melihat atau mengedit jadwal/nilai jurusan PPLG dengan mengganti URL: `GET /kaprog/jadwal?id_jurusan=2`.
  - **Skenario 2 (Wali Kelas):** Wali Kelas 10 DKV 1 mengganti parameter di form input menjadi `id_kelas=3` (11 DKV 1) untuk memanipulasi nilai kelas lain.
  - **Skenario 3 (Siswa):** Siswa mengganti URL transkrip nilai menjadi `GET /siswa/nilai?id_siswa=4` untuk mengintip nilai siswa lain.
- **Mekanisme Proteksi:**
  - **Parameter ID dari klien TIDAK PERNAH DIPERCAYA.**
  - Identitas jurusan Kaprog, kelas binaan Wali Kelas, dan ID Siswa **WAJIB DIAMBIL DARI DATA SESI LOGIN YANG TERVERIFIKASI DI DATABASE**, bukan dari URL string `$_GET` atau formulir `$_POST`.
  - Jika pengguna mengirimkan parameter ID objek, sistem menjalankan **Query Validasi Kepemilikan (*Ownership Check*)**. Jika ID tersebut di luar wewenang pengguna, sistem segera memblokir request dan melempar `HTTP 403 Forbidden`.

#### Kode Implementasi Pencegahan IDOR:

```php
// Contoh 1: Pengamanan Controller Nilai Siswa (Hanya Siswa Terkait yang Boleh Melihat)
public function tampilkanRaporSiswa(int $idSiswaRequest): void {
    $idPenggunaLogin = $_SESSION['pengguna_id'];
    $kodePeran = $_SESSION['kode_peran'];

    if ($kodePeran === 'siswa') {
        // Ambil ID siswa yang terikat dengan akun login saat ini
        $idSiswaMilikLogin = $this->db->query(
            "SELECT id FROM siswa WHERE id_pengguna = :id_pengguna LIMIT 1",
            [':id_pengguna' => $idPenggunaLogin]
        )->fetchColumn();

        // Validasi Anti-IDOR: Jika mencoba melihat ID orang lain, tolak seketika!
        if ((int)$idSiswaRequest !== (int)$idSiswaMilikLogin) {
            AuditLogger::catat($this->db, $idPenggunaLogin, 'PERCOBAAN_IDOR_SISWA', 'siswa', $idSiswaRequest);
            http_response_code(403);
            die("Akses Ditolak: Anda tidak memiliki wewenang melihat data siswa lain.");
        }
    }
}

// Contoh 2: Pengamanan Dasbor Kaprog (Query Terkunci Berdasarkan Jurusan)
public function ambilNilaiJurusan(int $idKelasInput): array {
    $idPenggunaLogin = $_SESSION['pengguna_id'];

    // Ambil ID jurusan milik Kaprog ini dari database
    $idJurusanKaprog = $this->db->query(
        "SELECT j.id FROM jurusan j 
         JOIN guru g ON j.id_guru_kaprog = g.id 
         WHERE g.id_pengguna = :id_pengguna LIMIT 1",
        [':id_pengguna' => $idPenggunaLogin]
    )->fetchColumn();

    // Verifikasi apakah id_kelas yang diminta benar berada di bawah jurusan sang Kaprog
    $apakahKelasMilikJurusan = $this->db->query(
        "SELECT COUNT(*) FROM kelas WHERE id = :id_kelas AND id_jurusan = :id_jurusan",
        [':id_kelas' => $idKelasInput, ':id_jurusan' => $idJurusanKaprog]
    )->fetchColumn();

    if (!$apakahKelasMilikJurusan) {
        AuditLogger::catat($this->db, $idPenggunaLogin, 'PERCOBAAN_IDOR_KAPROG', 'kelas', $idKelasInput);
        http_response_code(403);
        die("Akses Ditolak: Kelas ini bukan bagian dari kewenangan jurusan Anda.");
    }

    // Jika valid, eksekusi query nilai
    return $this->db->query(
        "SELECT * FROM nilai_siswa WHERE id_penugasan_guru IN (SELECT id FROM penugasan_guru WHERE id_kelas = :id_kelas)",
        [':id_kelas' => $idKelasInput]
    )->fetchAll();
}
```

---

### 2.5. Pencegahan SQL Injection (SQLi)
- **Ancaman:** Penyerang menyisipkan sintaks SQL berbahaya melalui formulir input atau URL parameter (contoh: `' OR 1=1 --`) untuk merusak database atau melewati otentikasi.
- **Mekanisme Proteksi:**
  1. Seluruh operasi basis data wajib menggunakan **PDO Prepared Statements** dengan parameter binding.
  2. Nonaktifkan emulasi prepared statements di PDO:
     ```php
     $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     ```
  3. Untuk kolom dinamis yang tidak dapat di-bind (seperti `ORDER BY kolom ASC`), wajib menggunakan teknik **Whitelisting**:
     ```php
     $kolomDiizinkan = ['nama_lengkap', 'nis', 'created_at'];
     $orderBy = in_array($_GET['sort'] ?? '', $kolomDiizinkan, true) ? $_GET['sort'] : 'nama_lengkap';
     ```

---

### 2.6. Pencegahan Cross-Site Scripting (XSS)
- **Ancaman:** Penyerang memasukkan script JavaScript berbahaya ke dalam kolom teks (misal: deskripsi event, catatan wali kelas, alamat siswa) yang akan tereksekusi saat halaman dibuka oleh pengguna lain.
- **Mekanisme Proteksi:**
  1. **Output Encoding / Escaping:** Setiap data dinamis yang ditampilkan ke antarmuka wajib dibungkus fungsi escape:
     ```php
     function e(?string $string): string {
         return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
     }
     ```
  2. **Sanitasi Rich Text Deskripsi Event:** Khusus untuk deskripsi event yang mendukung format tebal/miring, gunakan library HTML Purifier atau `strip_tags()` dengan atribut yang sangat terbatas.
  3. **Header HTTP Content Security Policy (CSP):**
     ```http
     Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';
     ```

---

### 2.7. Perlindungan Cross-Site Request Forgery (CSRF)
- **Ancaman:** Pengguna yang sedang login diarahkan membuka link berbahaya di tab lain yang secara diam-diam mengeksekusi request mutasi (misal menghapus jadwal atau menyetujui event).
- **Mekanisme Proteksi:**
  1. Setiap sesi pengguna menghasilkan token acak kriptografis 32-byte:
     ```php
     if (empty($_SESSION['csrf_token'])) {
         $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
     }
     ```
  2. Setiap formulir HTML dengan metode `POST`, `PUT`, atau `DELETE` wajib menyertakan input tersembunyi:
     ```html
     <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
     ```
  3. Middleware memvalidasi token menggunakan fungsi anti-timing attack `hash_equals()`:
     ```php
     if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
         http_response_code(419);
         die("Sesi kedaluwarsa atau token CSRF tidak valid. Silakan muat ulang halaman.");
     }
     ```

---

### 2.8. Validasi Ketat Upload Foto & Poster (OSIS & Pasfoto Siswa)
- **Ancaman:** Penyerang mengunggah berkas web shell berekstensi ganda (misal: `backdoor.php.jpg`), berkas executable tersembunyi, atau file SVG yang mengandung payload script XSS.
- **Mekanisme Proteksi 6 Lapis (*6-Layer Defense*):**
  1. **Pemeriksaan Ekstensi Whitelist:** Hanya mengizinkan `.jpg`, `.jpeg`, `.png`, dan `.webp`. Menolak keras `.svg`, `.php`, `.exe`, `.sh`.
  2. **Pemeriksaan MIME Type Nyata (*Magic Bytes*):** Memeriksa header biner berkas menggunakan PHP `finfo_file()` / `mime_content_type()`, bukan sekadar membaca ekstensi.
  3. **Verifikasi Validitas Gambar:** Memastikan berkas adalah gambar asli menggunakan fungsi `getimagesize()`.
  4. **Pembersihan Metadata (EXIF Stripping):** Menghapus data EXIF GPS kamera untuk menjaga privasi lokasi.
  5. **Pengacakan Nama Berkas (*Random Renaming*):** Nama asli berkas dibuang dan diganti hash acak:
     `poster_event_` + `bin2hex(random_bytes(16))` + `.` + `ekstensi`.
  6. **Isolasi Folder Penyimpanan:** Folder `public/uploads/event/` diberi aturan server untuk **menonaktifkan eksekusi skrip PHP** (`php_flag engine off` di `.htaccess`).

#### Kode Implementasi Validasi Upload Berkas:
```php
class PengunggahBerkas {
    private const EKSTENSI_VALID = ['jpg', 'jpeg', 'png', 'webp'];
    private const MIME_VALID = ['image/jpeg', 'image/png', 'image/webp'];
    private const UKURAN_MAKSIMAL = 2 * 1024 * 1024; // 2 MegaByte

    public static function unggahPosterEvent(array $file): string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Terjadi kesalahan saat mengunggah berkas poster.");
        }

        if ($file['size'] > self::UKURAN_MAKSIMAL) {
            throw new Exception("Ukuran foto poster melebihi batas maksimal 2 MB.");
        }

        // 1. Cek Ekstensi
        $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ekstensi, self::EKSTENSI_VALID, true)) {
            throw new Exception("Format berkas tidak diizinkan. Gunakan JPG, PNG, atau WebP.");
        }

        // 2. Cek MIME Type Nyata
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeTipe = $finfo->file($file['tmp_name']);
        if (!in_array($mimeTipe, self::MIME_VALID, true)) {
            throw new Exception("Berkas yang diunggah bukan gambar valid.");
        }

        // 3. Cek Dimensi Gambar
        if (@getimagesize($file['tmp_name']) === false) {
            throw new Exception("Integritas gambar rusak atau terindikasi disusupi skrip.");
        }

        // 4. Generate Nama Acak Aman
        $namaBerkasBaru = 'poster_event_' . bin2hex(random_bytes(12)) . '.' . $ekstensi;
        $tujuan = __DIR__ . '/../../public/uploads/event/' . $namaBerkasBaru;

        if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
            throw new Exception("Gagal menyimpan berkas ke server.");
        }

        return $namaBerkasBaru;
    }
}
```

---

### 2.9. Pembatasan Akses Langsung ke File dan Halaman
- **Ancaman:** Pengunjung langsung mengakses berkas internal seperti `http://sekolah.id/app/config/database.php` atau `http://sekolah.id/views/siswa/nilai.php`.
- **Mekanisme Proteksi:**
  1. **Konfigurasi Web Server Document Root:** Virtual host web server (Apache/Nginx) diarahkan secara mutlak ke folder `public/`. Dengan demikian, folder `app/`, `views/`, dan `config/` tidak berada di pohon direktori publik.
  2. **Proteksi Konstanta Eksekusi:** Setiap file PHP di dalam folder selain `public/` wajib diawali pemeriksaan konstanta:
     ```php
     defined('AKSES_SIAKAD') or die('Akses langsung ke berkas ini dilarang!');
     ```
  3. **Pencegahan Path Traversal:** Menggunakan fungsi `basename()` pada nama file unduhan untuk mencegah penyerang menyisipkan karakter `../../` guna mencuri berkas konfigurasi sistem.

---

### 2.10. Proteksi Brute Force Login
- **Ancaman:** Penyerang menggunakan program otomatis (*bot*) untuk menebak kata sandi pengguna secara berulang-ulang (*dictionary attack*).
- **Mekanisme Proteksi:**
  1. **Pencatatan Frekuensi Percobaan Gagal:** Setiap kali login gagal, sistem mencatat alamat IP dan username ke cache atau tabel pencatat.
  2. **Ambang Batas (*Threshold*):** Maksimal **5 kali kegagalan** dalam kurun waktu 15 menit.
  3. **Penguncian Sementara (*Temporary Lockout*):** Jika melebihi 5 kali, akun atau alamat IP tersebut dibekukan selama **15 menit**.
  4. **Tarpitting (Artificial Delay):** Menambahkan penundaan respons (*delay*) selama 1 detik pada setiap kegagalan login untuk memperlambat laju penyerangan otomatis.

---

### 2.11. Audit Logging Komprehensif (`log_audit`)
- **Fungsi:** Menyediakan jejak forensik hukum yang tidak dapat disangkal (*non-repudiation*) jika terjadi manipulasi nilai atau kebocoran data.
- **Ketentuan Audit:**
  1. Bersifat *Append-Only* (Tidak ada fitur edit atau hapus log di aplikasi).
  2. Merekam perubahan data kritis:
     - Finalisasi / pembukaan kunci nilai rapor (`nilai_siswa`).
     - Pemindahan rombel siswa atau mutasi status (`riwayat_kelas_siswa`).
     - Persetujuan atau penolakan event sekolah (`event_sekolah`).
     - Perubahan penugasan wali kelas dan guru (`penugasan_wali_kelas`).
  3. Menyimpan perbandingan *snapshot* data lama (`nilai_lama`) dan data baru (`nilai_baru`) dalam format JSON.

---

### 2.12. Backup Database Terjadwal & Pemulihan (Disaster Recovery)
- **Ancaman:** Kerusakan perangkat keras (*hardware failure*), serangan ransomware, atau kesalahan manusia (*human error*) yang menghapus basis data `db_smkn9`.
- **Mekanisme Proteksi:**
  1. **Otomatisasi Backup Harian (Cron Job / Windows Task Scheduler):**
     Menjalankan perintah `mysqldump` terkompresi setiap pukul 02:00 dini hari:
     ```bash
     mysqldump -u siakad_user -p'SecretPass123' db_smkn9 | gzip > /backups/db_smkn9_$(date +\%Y\%m\%d_\%H\%M).sql.gz
     ```
  2. **Enkripsi Cadangan:** Berkas backup dienkripsi menggunakan AES-256 sebelum disimpan ke media penyimpanan terpisah (*off-site cloud storage*).
  3. **Penyusutan Riwayat (Retention Policy):**
     - Backup harian disimpan selama 7 hari.
     - Backup mingguan disimpan selama 4 minggu.
     - Backup akhir semester disimpan permanen untuk arsip akademik.
  4. **Uji Coba Pemulihan (*Restoration Drill*):** Dilakukan uji coba import berkala ke server uji coba setiap 3 bulan sekali.

---

### 2.13. Penanganan Error Tanpa Membocorkan Informasi Sensitif
- **Ancaman:** Pesan error default PHP/MySQL membocorkan path direktori server (`C:\xampp\htdocs\web-komet\...`), struktur nama tabel, nomor port database, atau potongan query SQL yang dapat dimanfaatkan penyerang untuk merancang eksploitasi.
- **Mekanisme Proteksi:**
  1. **Konfigurasi Lingkungan Produksi (`config/app.php`):**
     ```php
     ini_set('display_errors', '0');
     ini_set('display_startup_errors', '0');
     error_reporting(E_ALL);
     ini_set('log_errors', '1');
     ini_set('error_log', __DIR__ . '/../logs/app_errors.log');
     ```
  2. **Penangkap Kesalahan Terpusat (*Global Exception Handler*):**
     Seluruh error tak terduga ditangkap dan diarahkan ke halaman kustom yang ramah pengguna (*Custom Error Pages: 404, 403, 500*):
     *"Maaf, terjadi kendala teknis pada sistem. Kode referensi error telah dicatat untuk ditindaklanjuti oleh tim IT Kesiswaan."*

---

### 2.14. Perlindungan Data Pribadi Siswa (Kepatuhan UU PDP)
- **Prinsip Dasar:** Menjaga kerahasiaan data siswa di bawah umur sesuai standar kepatuhan privasi pendidikan.
- **Aturan Kebijakan:**
  1. **Pembatasan Visibilitas Data:** Alamat rumah, nomor telepon orang tua, dan tanggal lahir siswa dikategorikan sebagai **Data Privat**. Hanya Kesiswaan dan Wali Kelas dari siswa bersangkutan yang dapat melihat data ini.
  2. **Penyamaran Data Sensitif (*Data Masking*):** Pada tabel umum, nomor telepon orang tua disamarkan sebagian (contoh: `0813****9012`).
  3. **Pemisahan Rapor Teman:** Siswa **dilarang keras** memiliki antarmuka yang menampilkan daftar nilai teman sekelasnya. Siswa hanya dapat mengakses transkrip miliknya sendiri.

---

## 3. Matriks Pemodelan Ancaman (Threat Modeling Matrix)

| Skenario Serangan / Ancaman | Sasaran Objek | Tingkat Risiko | Mitigasi yang Diterapkan di SIAKAD SMKN 9 |
| :--- | :--- | :--- | :--- |
| **Manipulasi URL Parameter (IDOR)** | Transkrip Nilai & Jadwal | **KRITIS** | Server-side ownership validation; ID siswa/kelas diambil dari sesi login yang sah di database, bukan dari input URL. |
| **Upload Script Web Shell** | Mading Event OSIS | **KRITIS** | Whitelist ekstensi, MIME type magic-byte check, validasi GD image, random file renaming, dan eksekusi PHP dimatikan di folder upload. |
| **Pencurian Sesi (Session Hijacking)** | Akun Kesiswaan & Guru | **TINGGI** | Cookie HttpOnly, Secure, SameSite=Lax, regenerasi ID sesi saat login, dan deteksi pergantian User-Agent. |
| **SQL Injection (Bypass Login)** | Form Login & Pencarian | **TINGGI** | Wajib menggunakan PDO Prepared Statements dengan *real parameterized binding* (emulasi dinonaktifkan). |
| **Scripting Jahat Lintas Situs (XSS)** | Deskripsi Event & Profil | **TINGGI** | Output escaping menyeluruh dengan `htmlspecialchars()`, Content Security Policy (CSP), dan pembersihan tag HTML. |
| **Serangan Bot Kamus (Brute Force)** | Form Login Pengguna | **SEDANG** | Rate limiting maksimal 5 kali gagal per 15 menit, penguncian akun sementara, dan penundaan respons (*delay*). |
| **Serangan Pemalsuan Request (CSRF)** | Pengesahan Nilai Rapor | **SEDANG** | Token CSRF acak unik berbasis sesi yang diverifikasi secara timing-safe pada setiap mutasi POST/DELETE. |
| **Kebocoran Path & Struktur Query** | Error Halaman Web | **RENDAH** | Penonaktifan `display_errors` pada server publik dan pencatatan pesan teknis ke berkas log tersembunyi `logs/app_errors.log`. |

