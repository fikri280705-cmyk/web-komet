# PANDUAN DESAIN SISTEM UI/UX & STRUKTUR WIREFRAME
## SISTEM INFORMASI AKADEMIK (SIAKAD) SMK NEGERI 9 TANGERANG
**Kode Proyek:** `web-komet` | **Database:** `db_smkn9` | **Aset Logo:** `web-komet/images/logosmkn9.png`

---

## 1. Identitas Visual & Design Tokens (Sistem Palet Warna Resmi)

Desain antarmuka SIAKAD SMKN 9 Tangerang mengadopsi palet warna modern, bersih, dan berwibawa khas institusi pendidikan vokasi:

```
+-----------------------------------------------------------------------------------+
|  PALET WARNA RESMI SIAKAD SMKN 9 TANGERANG                                         |
+-----------------------------------------------------------------------------------+
|  #112E81  | Primary Navy        | Navbar, Header Utama, Brand Sidebar, Dark Mode BG|
|  #4647AE  | Royal Indigo        | Gradient Accent, Active Pill, Sub-heading, Hover |
|  #4382DF  | Vibrant Blue        | Primary Button, Hyperlink, Active Tab, Focus Ring|
|  #AACCD6  | Soft Pastel Aqua    | Light Badge, Selected Row, Muted Card Background |
+-----------------------------------------------------------------------------------+
|  #0F172A  | Slate Dark 900      | Warna Teks Utama (Heading & Konten Pokok)        |
|  #475569  | Slate Medium 600    | Warna Teks Muted (Label, Metadata, Timestamp)    |
|  #F8FAFC  | Slate Light 50      | Warna Latar Belakang Aplikasi (Canvas Background)|
|  #FFFFFF  | Pure White          | Warna Permukaan Kartu (Card Surface), Modal, Form|
|  #E2E8F0  | Slate Border 200    | Warna Garis Batas Tabel, Separator, Border Input |
+-----------------------------------------------------------------------------------+
|  #10B981  | Emerald (Success)   | Status 'final', 'published', 'aktif', Notif Sukses|
|  #F59E0B  | Amber (Warning)     | Status 'pending', 'tinggal', Konfirmasi Hati-hati |
|  #EF4444  | Rose (Danger)       | Status 'rejection', Hapus Data, Alert Error Fatal|
|  #6B7280  | Cool Gray (Neutral) | Status 'draft', 'archived', Tombol Batal         |
+-----------------------------------------------------------------------------------+
```

### Tipografi & Skala Teks (Font Family: *Plus Jakarta Sans* / *Inter*):
- **Display 1 (24px - 30px / Bold):** Judul Dasbor dan Halaman Utama.
- **Heading 2 (18px - 20px / SemiBold):** Judul Kartu Widget, Bagian Tabel, Header Modal.
- **Body Text (14px - 15px / Regular):** Isi tabel, paragraf, deskripsi event, input formulir.
- **Caption / Small (12px / Medium):** Badge status, timestamp audit, petunjuk validasi error.

---

## 2. Strategi Responsif (Desktop, Tablet, Mobile Android & iOS)

Sistem dirancang dengan pendekatan **Mobile-First Responsive Design** agar dapat dibuka dengan nyaman oleh guru di laptop ruang guru maupun oleh siswa/orang tua dari smartphone Android/iOS:

1. **Desktop View ($\ge 1024\text{px}$):**
   - Sidebar navigasi tetap di sisi kiri (*fixed sidebar width 260px*).
   - Top navbar berisi status tahun ajaran aktif, pencarian global cepat, notifikasi lonceng, dan profil user dropdown.
   - Konten utama tersaji multi-kolom (*grid 3 atau 4 kolom untuk KPI cards*).
   - Tabel data menampilkan kolom lengkap secara leluasa.
2. **Tablet View ($768\text{px} - 1023\text{px}$):**
   - Sidebar dapat diciutkan (*collapsed*) menjadi mode ikon.
   - Grid dasbor beralih menjadi 2 kolom.
   - Tabel mengaktifkan *horizontal scrollbar* halus tanpa merusak layout luar.
3. **Smartphone View ($\le 767\text{px}$ - Android & iOS):**
   - Sidebar disembunyikan dalam *Off-canvas Drawer* (dibuka via tombol hamburger).
   - Menu aksi utama siswa (Jadwal, Nilai, Event) didukung oleh *Bottom Navigation Bar* yang menempel di bagian bawah layar smartphone (*native app experience*).
   - Tabel data beralih otomatis menjadi format **Card-List View** atau tabel dengan horizontal scroll responsif.
   - Tombol sentuh (*touch target*) memiliki ukuran minimal $44 \times 44\text{px}$ agar mudah ditekan jari.

---

## 3. Spesifikasi Komponen Standar & State Handling

### 3.1. Tabel Data Interaktif
- **Pencarian Cepat:** Input debounced 300ms dengan icon kaca pembesar.
- **Filter Multi-Kriteria:** Dropdown Tahun Ajaran, Semester, Jurusan, dan Status.
- **Paginasi Cerdas:** Menampilkan dropdown limit (10, 25, 50 entri) dan navigasi halaman `[Sebelumnya] 1 2 3 [Selanjutnya]`.
- **Feedback State:**
  - *Loading State:* Menampilkan 5 baris **Skeleton Shimmer** beranimasi lembut (tidak blank putih).
  - *Empty State:* Ilustrasi folder kosong, teks ramah (*"Belum ada data nilai pada semester ini"*), dan tombol CTA jika user memiliki wewenang input.

### 3.2. Formulir & Validasi
- Pesan validasi error muncul langsung di bawah field input terkait dengan warna merah `#EF4444` dan icon peringatan.
- Tombol simpan otomatis beralih menampilkan spinner pemuatan (*loading indicator*) saat request diproses guna mencegah *double submission*.

### 3.3. Dialog Konfirmasi Modal
- Seluruh tindakan destruktif (Hapus Jadwal, Hapus Siswa) dan tindakan penguncian (Finalisasi Nilai Rapor) mewajibkan modal konfirmasi dengan teks penjelas konsekuensi aksi sebelum dieksekusi.

---

## 4. Perancangan Struktur Halaman & Wireframe Terperinci

Berikut adalah rancangan detail ke-13 antarmuka halaman yang disyaratkan:

---

### Halaman 1: Login Multi-Role
- **Hak Akses:** Publik / Tamu (Belum Terotentikasi).
- **Layout:** Split-screen pada desktop (50% ilustrasi branding SMKN 9 Tangerang dengan background `#112E81`, 50% form login bersih putih). Layar penuh pada mobile dengan logo di bagian atas.
- **Komponen:**
  - Logo resmi SMKN 9 Tangerang (`logosmkn9.png`)
  - Headline: *"SIAKAD SMKN 9 Tangerang"*
  - Subtitle: *"Sistem Informasi Akademik & Portal Layanan Sekolah Terpadu"*
  - Input Username / Email
  - Input Password dengan toggle mata (Show/Hide)
  - Remember Me Checkbox & Tautan "Lupa Password?"
  - Tombol Primer: "Masuk ke Sistem" (Warna `#112E81`, hover `#4382DF`)
  - Quick Info Footer: Layanan Bantuan Kesiswaan & IT Support.

#### Wireframe Teks:
```
+---------------------------------------------------------------------------------+
|                                  HALAMAN LOGIN                                  |
+----------------------------------------+----------------------------------------+
| [BRANDING AREA - Background #112E81]   | [FORM CONTAINER - Background #FFFFFF]  |
|                                        |                                        |
|   [LOGO SMKN 9 TANGERANG]              |   Selamat Datang Kembali!              |
|   SIAKAD SMKN 9 TANGERANG              |   Silakan masukkan kredensial akun Anda|
|   Pusat Layanan Akademik Kejuruan      |                                        |
|   Unggul, Berkarakter & Digital        |   +----------------------------------+ |
|                                        |   | Username atau Email Sekolah      | |
|   - Akses Nilai & Rapor Digital        |   +----------------------------------+ |
|   - Jadwal KBM Terintegrasi            |   +----------------------------------+ |
|   - Informasi Kegiatan Sekolah & OSIS  |   | Kata Sandi               [Show]  | |
|                                        |   +----------------------------------+ |
|                                        |   [X] Ingat Sesi Saya  [Lupa Password?]|
|                                        |                                        |
|                                        |   +----------------------------------+ |
|                                        |   |    MASUK KE DASHBOARD  -->       | |
|                                        |   +----------------------------------+ |
|                                        |                                        |
|                                        |   Butuh bantuan? Hubungi IT Kesiswaan  |
+----------------------------------------+----------------------------------------+
```

---

### Halaman 2: Dashboard Kesiswaan (Admin Utama)
- **Hak Akses:** Khusus Role `kesiswaan`.
- **Layout:** Standar Dashboard (Sidebar kiri, Header atas, Main Content).
- **Isi Informasi:**
  - 4 Kartu KPI: Total Siswa Aktif, Total Guru/Wali Kelas, Rombel/Kelas, Usulan Event Menunggu Persetujuan (*Pending Counter*).
  - Quick Action Banner: [Tambah Siswa Baru], [Pusat Jadwal], [Review Usulan Event].
  - Grafik Distribusi Siswa per Jurusan (DKV, PPLG, TJKT, AKL).
  - Tabel Ringkas: 5 Usulan Event OSIS Terbaru yang butuh approval cepat.

#### Wireframe Teks:
```
+---------------------------------------------------------------------------------+
| [LOGO] SMKN 9 | TA: 2024/2025 (Ganjil)        [Cari...]  (Bell) [Drs. Hendra v] |
+---------------+-----------------------------------------------------------------+
| [Sidebar]     | DASHBOARD KESISWAAN & KURIKULUM                                 |
| - Dashboard * | Selamat datang, Bpk. Drs. Hendra Permana, M.Pd.                 |
| - Data Siswa  +-----------------------------------------------------------------+
| - Manajemen   | [Total Siswa] | [Guru & Staf] | [Rombel Aktif] | [Event Pending]|
|   Jurusan     |    1.150      |      68       |   32 Kelas     |   2 Usulan (!) |
| - Jadwal KBM  +---------------+---------------+----------------+----------------+
| - Verif Nilai |                                                                 |
| - Kelola Event| [STATUS PERSETUJUAN EVENT OSIS TERBARU]                         |
| - Audit Logs  | Judul Acara              | Pengusul | Diajukan  | Aksi          |
| - Pengaturan  | Class Meeting Futsal     | OSIS     | 14 Okt    | [Review Acara]|
|               | Workshop Portofolio DKV  | OSIS     | 12 Okt    | [Review Acara]|
|               +-----------------------------------------------------------------+
|               | [DISTRIBUSI SISWA PER JURUSAN]      [AKSI CEPAT ADMINISTRASI]   |
|               | - DKV  : 340 Siswa (6 Kelas)        | [+ Tambah Siswa Baru]     |
|               | - PPLG : 310 Siswa (6 Kelas)        | [Atur Penugasan Wali]     |
|               | - TJKT : 300 Siswa (6 Kelas)        | [Cetak Rekap Absensi]     |
+---------------+-----------------------------------------------------------------+
```

---

### Halaman 3: Dashboard Kaprog (Kepala Program Keahlian)
- **Hak Akses:** Khusus Role `kaprog`.
- **Isi Informasi:**
  - Header: Nama Jurusan yang diampu (e.g., *"Konsentrasi Keahlian: Desain Komunikasi Visual"*).
  - Indikator Rombel Binaan: Daftar 6 Kelas (10 DKV 1, 10 DKV 2, 11 DKV 1, 11 DKV 2, 12 DKV 1, 12 DKV 2).
  - Progress Bar Pengisian Nilai Guru Produktif DKV: Menampilkan rombel mana yang nilainya masih berstatus `draft` vs `final`.
  - Jadwal Penggunaan Lab Komputer Multimedia / Studio Foto hari ini.

#### Wireframe Teks:
```
+---------------------------------------------------------------------------------+
| [LOGO] SMKN 9 | Jurusan: DKV                   [Cari...]   [Siti Rahmawati, S.Sn]
+---------------+-----------------------------------------------------------------+
| [Sidebar]     | DASHBOARD KEPALA PROGRAM: DESAIN KOMUNIKASI VISUAL (DKV)        |
| - Dashboard * | Cakupan: 6 Rombel | 210 Siswa DKV | 8 Guru Kejuruan             |
| - Siswa DKV   +-----------------------------------------------------------------+
| - Jadwal DKV  | [PROGRES PENGINPUTAN NILAI JURUSAN DKV - SEMESTER GANJIL]       |
| - Nilai Mapel | 10 DKV 1 : [=========>        ] 65% (Sebagian Masih Draft)      |
| - Pengaturan  | 10 DKV 2 : [=============>    ] 85%                             |
|               | 11 DKV 1 : [==================] 100% (Semua Nilai Final/Kunci)  |
|               +-----------------------------------------------------------------+
|               | [JADWAL LAB & STUDIO HARI INI]        [AKSES CEPAT JURUSAN]     |
|               | 07:30 - Lab Desain 1 (10 DKV 1)       | [Review Nilai DKV]      |
|               | 10:15 - Lab Desain 2 (11 DKV 2)       | [Validasi Jadwal DKV]   |
|               | 13:00 - Studio Foto (12 DKV 1)        | [Daftar Siswa Binaan]   |
+---------------+-----------------------------------------------------------------+
```

---

### Halaman 4: Dashboard Wali Kelas
- **Hak Akses:** Khusus Role `wali_kelas`.
- **Isi Informasi:**
  - Banner Info: *"Wali Kelas 10 DKV 1 | Tahun Ajaran 2024/2025"*.
  - Metrik Kelas: Jumlah Siswa (36 Siswa), Rata-rata Nilai Rombel, Jumlah Siswa dengan Nilai Belum Lengkap.
  - Status Nilai Rapor Kelas: Indikator apakah seluruh mata pelajaran sudah berstatus `final` untuk siap dicetak rapornya.
  - Jadwal Mengajar & Jadwal Belajar Kelas Hari Ini.

#### Wireframe Teks:
```
+---------------------------------------------------------------------------------+
| [LOGO] SMKN 9 | Kelas: 10 DKV 1               [Cari...]     [Ahmad Fauzi, S.Pd.]|
+---------------+-----------------------------------------------------------------+
| [Sidebar]     | DASHBOARD WALI KELAS - 10 DKV 1                                 |
| - Dashboard * | Semester Ganjil 2024/2025 | Ruang Teori DKV 1                   |
| - Siswa Binaan+-----------------------------------------------------------------+
| - Nilai Rombel| [Total Siswa] | [Mapel Tuntas] | [Perlu Remedial] | [Status Rapor]|
| - Jadwal KBM  |   36 Siswa    |  8 / 10 Mapel  |     3 Siswa      | Draft Belum   |
| - Pengaturan  +---------------+----------------+------------------+---------------+
|               | [DAFTAR SISWA KELAS 10 DKV 1 MEMERLUKAN PERHATIAN KHUSUS]       |
|               | 1. Dimas Satria   - Nilai Praktik Desain Belum Diinput          |
|               | 2. Amanda Putri   - Rata-rata 92.5 (Peringkat 1 Sementara)      |
|               +-----------------------------------------------------------------+
|               | [AKSI WALI KELAS]                                               |
|               | [Buka Buku Nilai 10 DKV 1]   [Lihat Jadwal KBM Mingguan]        |
+---------------+-----------------------------------------------------------------+
```

---

### Halaman 5: Dashboard Siswa
- **Hak Akses:** Khusus Role `siswa`.
- **Layout Khusus Mobile:** Header ramah sapaan ("Halo, Rizky!"), jadwal KBM hari ini dengan countdown jam pelajaran, banner event terdekat sekolah, dan tombol pintas nilai rapor.
- **Isi Informasi:**
  - Identitas Siswa: Nama, NIS/NISN, Kelas Aktif (10 DKV 1), Jurusan DKV.
  - Widget: *"Mata Pelajaran Berikutnya Hari Ini"*.
  - Ringkasan Nilai Terkini (Hanya yang berstatus `final`).
  - Feed 2 Event Sekolah Terpopuler / Terbaru.

#### Wireframe Teks:
```
+---------------------------------------------------------------------------------+
| [LOGO] SMKN 9 | Portal Siswa                 (Lonceng 2) [Rizky Pratama (Siswa)]|
+---------------+-----------------------------------------------------------------+
| [Sidebar/Nav] | DASHBOARD SISWA                                                 |
| - Beranda *   | Halo, Muhammad Rizky Pratama! (NIS: 242510001 | Kelas 10 DKV 1) |
| - Rapor Nilai +-----------------------------------------------------------------+
| - Jadwal Saya | [JADWAL BELAJAR KAMU HARI INI - SENIN]                          |
| - Event SMKN 9| >> 07:30 - 09:45 : Dasar Desain Grafis (Lab Desain 1)           |
| - Profil Akun |    Guru: Bpk. Ahmad Fauzi, S.Pd.                                |
|               | >> 10:15 - 12:00 : Bahasa Indonesia (Ruang 10 DKV 1)            |
|               +-----------------------------------------------------------------+
|               | [EVENT SEKOLAH TERBARU]                                         |
|               | [*] Lomba Desain Poster Digital Dies Natalis (10 Nov 2024)      |
|               |     [Lihat Detail Pengumuman & Syarat Lomba]                    |
|               +-----------------------------------------------------------------+
|               | [NILAI TERBARU TERVERIFIKASI]                                   |
|               | - Dasar Desain Grafis : 89.20 [Grade: A] (Status: Sah/Final)    |
|               | - Bahasa Indonesia    : 85.00 [Grade: B] (Status: Sah/Final)    |
|               | [Buka Transkrip Nilai Lengkap >>]                               |
+---------------+-----------------------------------------------------------------+
```

---

### Halaman 6: Dashboard Asisten Sekolah (OSIS)
- **Hak Akses:** Khusus Role `osis`.
- **Isi Informasi:**
  - Status Pipeline Event: Draf Saya, Menunggu Verifikasi Kesiswaan, Telah Dipublikasikan, Diarsipkan.
  - Tombol Utama Aksi: `[+ Buat Usulan Event Baru]`.
  - Log Umpan Balik Kesiswaan: Menampilkan jika ada event yang ditolak beserta alasan revisinya.
  - Pembatasan Ketat: Tidak ada akses ke nilai, biodata internal, atau jadwal pelajaran guru.

#### Wireframe Teks:
```
+---------------------------------------------------------------------------------+
| [LOGO] SMKN 9 | Divisi Publikasi OSIS         [Cari Event]  [Nayla Zahra (OSIS)]|
+---------------+-----------------------------------------------------------------+
| [Sidebar]     | PUSAT INFORMASI & EVENT OSIS                                    |
| - Dashboard * | Kelola kegiatan, perlombaan, dan mading digital sekolah         |
| - Buat Event  +-----------------------------------------------------------------+
| - Event Saya  | [Draf Acara]  | [Menunggu Review] | [Dipublikasikan] | [Arsip]  |
| - Pengaturan  |    1 Event    |      1 Event (!)  |     2 Event      | 4 Event  |
|               +---------------+-------------------+------------------+----------+
|               | [DAFTAR USULAN EVENT SAYA & STATUS PERSETUJUAN]                 |
|               | Judul: Class Meeting Futsal & E-Sport                           |
|               | Status: [MENUNGGU PERSETUJUAN KESISWAAN] (Dikirim: 14 Okt 2024) |
|               | Catatan Kesiswaan: "Sedang koordinasi izin penggunaan lapangan" |
|               +-----------------------------------------------------------------+
|               | [AKSI CEPAT]                                                    |
|               | [+ Tulis Usulan Event Baru]      [Lihat Panduan Publikasi]      |
+---------------+-----------------------------------------------------------------+
```

---

### Halaman 7: Data Siswa (Master List Siswa)
- **Hak Akses:** Kesiswaan (Full CRUD), Kaprog (Read siswa jurusannya), Wali Kelas (Read siswa rombelnya).
- **Komponen:**
  - Bar Filter: Dropdown Jurusan, Dropdown Tingkat/Kelas, Input Pencarian NIS/Nama.
  - Tombol: [+ Tambah Siswa Baru] (Kesiswaan), [Ekspor Data], [Proses Kenaikan Rombel].
  - Tabel: Foto, NIS, NISN, Nama Lengkap, Jurusan, Kelas Aktif, Status, Aksi (Detail, Edit, Nonaktifkan).

#### Wireframe Teks:
```
+---------------------------------------------------------------------------------+
| DATA INDUK SISWA                                       [+ Tambah Siswa] [Ekspor]|
+---------------------------------------------------------------------------------+
| Filter: [Semua Jurusan v]  [Kelas: 10 DKV 1 v]  [Status: Aktif v]  [Cari Nama..]|
+---------------------------------------------------------------------------------+
| Foto | NIS       | NISN       | Nama Siswa          | Rombel   | Status | Aksi  |
+------+-----------+------------+---------------------+----------+--------+-------+
| [O]  | 242510001 | 0081234561 | M. Rizky Pratama    | 10 DKV 1 | Aktif  | [Eye] |
| [O]  | 242510002 | 0081234562 | Amanda Putri L.     | 10 DKV 1 | Aktif  | [Eye] |
| [O]  | 242510003 | 0081234563 | Dimas Satria W.     | 10 DKV 1 | Aktif  | [Eye] |
+------+-----------+------------+---------------------+----------+--------+-------+
| Menampilkan 1 - 3 dari 36 siswa di kelas 10 DKV 1        [Prev] 1 [2] [3] [Next]|
+---------------------------------------------------------------------------------+
```

---

### Halaman 8: Detail Profil Siswa & Riwayat Akademik
- **Hak Akses:** Kesiswaan, Kaprog terkait, Wali Kelas terkait, dan Siswa terkait (Profil Pribadi).
- **Layout:** Header Kartu Profil (Foto, NIS/NISN, Status) + Sistem Tab:
  - Tab 1: Biodata Lengkap (Data diri, alamat, kontak orang tua).
  - Tab 2: Riwayat Kelas & Penempatan (Tahun 2023/2024 -> 10 DKV 1, Tahun 2024/2025 -> 11 DKV 1).
  - Tab 3: Rapor Akademik Kumulatif.

#### Wireframe Teks:
```
+---------------------------------------------------------------------------------+
| PROFIL SISWA: MUHAMMAD RIZKY PRATAMA                                 [<- Kembali|
+---------------------------------------------------------------------------------+
| [FOTO SISWA]  | Nama Lengkap : Muhammad Rizky Pratama                           |
| 3x4 Resmi     | NIS / NISN   : 242510001 / 0081234561                           |
|               | Jurusan      : Desain Komunikasi Visual (DKV)                   |
|               | Status       : [AKTIF] | Angkatan: 2024                         |
+---------------+-----------------------------------------------------------------+
| [Tab 1: Biodata Pribadi] *  [Tab 2: Riwayat Kenaikan Rombel]  [Tab 3: Buku Nilai|
+---------------------------------------------------------------------------------+
| Tempat, Tgl Lahir : Tangerang, 14 Mei 2008                                      |
| Jenis Kelamin     : Laki-laki                                                   |
| Alamat Tempat Tinggal: Jl. Merdeka No. 45, Karawaci, Kota Tangerang             |
| Nomor Telepon/WA  : 089612340001                                                |
| Nama Orang Tua    : Bambang Pratama                                             |
| No. HP Orang Tua  : 081388880001 (Kontak Darurat)                               |
+---------------------------------------------------------------------------------+
```

---

### Halaman 9: Nilai Siswa (Input, Rapor, & Pengesahan Draft/Final)
- **Hak Akses:** Kesiswaan (Full), Kaprog (Jurusan), Wali Kelas (Rombel), Guru (Mapel diampu). Siswa (Read-only status final).
- **Komponen:**
  - Filter: Tahun Ajaran (2024/2025), Semester (Ganjil), Kelas (10 DKV 1), Mata Pelajaran (Dasar Desain Grafis).
  - Bar Status: Menampilkan badge `[STATUS: DRAFT]` atau `[STATUS: FINAL (TERKUNCI)]`.
  - Tabel Input Model Spreadsheet: No, NIS, Nama, Tugas (30%), UTS (20%), UAS (20%), Praktik (30%), Nilai Akhir Otomatis, Predikat.
  - Aksi: `[Simpan Draft]`, `[Kunci & Sahkan Nilai (Final)]`.

#### Wireframe Teks:
```
+---------------------------------------------------------------------------------+
| PENGELOLAAN NILAI AKADEMIK                                                      |
+---------------------------------------------------------------------------------+
| Filter: [TA: 2024/2025 v] [Semester Ganjil v] [Kelas: 10 DKV 1 v] [Dasar Desain]|
| Status Penilaian Rombel: [ DRAFT (Belum Terbit ke Siswa) ]                      |
+---------------------------------------------------------------------------------+
| No | NIS       | Nama Siswa       | Tugas | UTS  | UAS  | Praktik | Akhir | Mutu|
+----+-----------+------------------+-------+------+------+---------+-------+-----+
| 1  | 242510001 | M. Rizky Pratama | [88 ] | [85] | [90] | [92   ] | 89.20 |  A  |
| 2  | 242510002 | Amanda Putri L.  | [92 ] | [90] | [95] | [94   ] | 92.90 |  A  |
| 3  | 242510003 | Dimas Satria W.  | [75 ] | [70] | [78] | [80   ] | 76.10 |  B  |
+----+-----------+------------------+-------+------+------+---------+-------+-----+
| [!] Catatan: Mengesahkan nilai akan menjadikannya FINAL dan dapat dilihat siswa.|
| [Batal]                     [Simpan Sebagai Draft]      [KUNCI & SAHKAN (FINAL)]|
+---------------------------------------------------------------------------------+
```

---

### Halaman 10: Jadwal Pelajaran (Grid Mingguan & Anti-Bentrok)
- **Hak Akses:** Kesiswaan & Kaprog (Kelola), Guru & Siswa (Lihat).
- **Tampilan:** Pilihan mode tampilan: `[Tampilan Tabel Mingguan]` / `[Tampilan Kalender Slot Jam]`.
- **Fitur Validasi:** Saat form jadwal dibuka, sistem menampilkan indikator ketersediaan guru dan lab secara *real-time*.

#### Wireframe Teks:
```
+---------------------------------------------------------------------------------+
| JADWAL PELAJARAN KELAS 10 DKV 1                         [+ Tambah Jam Pelajaran]|
+---------------------------------------------------------------------------------+
| Filter: [Tahun: 2024/2025 Ganjil v]  [Jurusan: DKV v]  [Kelas: 10 DKV 1 v]      |
+---------------------------------------------------------------------------------+
| HARI   | JAM KE | WAKTU         | MATA PELAJARAN           | GURU PENGAJAR      | RUANG        |
+--------+--------+---------------+--------------------------+--------------------+--------------+
| SENIN  | 1 - 3  | 07:30 - 09:45 | Dasar Desain Grafis      | Ahmad Fauzi, S.Pd. | Lab Desain 1 |
|        | 4 - 5  | 10:15 - 12:00 | Bahasa Indonesia         | Drs. Hendra P.     | R. Teori DKV |
| SELASA | 1 - 3  | 07:30 - 10:00 | Tipografi & Ilustrasi    | Siti Rahmawati     | Lab Desain 2 |
| RABU   | 2 - 4  | 08:00 - 10:15 | Matematika Terapan       | Budi Santoso       | R. Teori DKV |
+---------------------------------------------------------------------------------+
```

---

### Halaman 11: Daftar & Detail Event Sekolah
- **Hak Akses:** Publik, Siswa, Guru (Melihat event `published`), Kesiswaan & OSIS (Melihat semua status).
- **Tampilan Kartu (Grid View):** Poster image, pill kategori warna lembut (`#AACCD6`), tanggal pelaksanaan, badge status, judul tebal, deskripsi singkat, tombol [Baca Selengkapnya].

#### Wireframe Teks:
```
+---------------------------------------------------------------------------------+
| AGENDA & KEGIATAN SMKN 9 TANGERANG                      [+ Buat Usulan Event]   |
+---------------------------------------------------------------------------------+
| Kategori: [Semua Kategori] [Akademik] [Lomba & Seni] [OSIS] [Pengumuman Resmi]  |
+---------------------------------------------------------------------------------+
| [CARD EVENT 1 - PUBLISHED]              | [CARD EVENT 2 - PUBLISHED]            |
| +-------------------------------------+ | +-----------------------------------+ |
| | [POSTER: LOMBA POSTER DIES NATALIS] | | | [POSTER: CLASS MEETING 2024]      | |
| +-------------------------------------+ | +-----------------------------------+ |
| Kategori : [Lomba & Kejuaraan]        | | Kategori : [Organisasi & OSIS]      | |
| Tanggal  : 10 November 2024           | | Tanggal  : 16 - 19 Desember 2024    | |
| Lokasi   : Aula Graha SMKN 9          | | Lokasi   : Lapangan Serbaguna       | |
| Judul    : Lomba Desain Poster Digital| | Judul    : Class Meeting Semester   | |
| Penyelenggara: Pokja DKV & OSIS       | | Penyelenggara: Pengurus OSIS        | |
| [Detail Pengumuman >>]                | | [Detail Pengumuman >>]              | |
+-----------------------------------------+---------------------------------------+
```

---

### Halaman 12: Formulir Pengelolaan & Verifikasi Event
- **Hak Akses:** OSIS (Membuat draf & mengajukan), Kesiswaan (Membuat, menyetujui, menolak, mengarsipkan).
- **Komponen Form:**
  - Input Judul Acara & Slug Otomatis.
  - Dropdown Kategori Event.
  - Form Datetime (Mulai dan Selesai).
  - Input Lokasi Kegiatan & Penyelenggara.
  - Unggah Poster Acara (Drag & Drop box dengan preview gambar).
  - Rich Text Editor: Deskripsi kegiatan dan rundown.
  - Box Khusus Kesiswaan: Approval Switch (`[Setujui & Publikasikan]`, `[Kembalikan Revisi ke OSIS]`).

#### Wireframe Teks:
```
+---------------------------------------------------------------------------------+
| FORMULIR PENGAJUAN / PENGELOLAAN EVENT                               [<- Batal] |
+---------------------------------------------------------------------------------+
| Judul Kegiatan    : [ Turnamen Futsal & E-Sport Class Meeting 2024            ] |
| Kategori Event    : [ Organisasi & OSIS                              v]         |
| Tanggal & Jam Mulai: [ 16/12/2024 ] Jam: [ 07:30 ] s/d [ 19/12/2024 ] Jam: [15:00]
| Lokasi Kegiatan   : [ Lapangan Serbaguna & Aula Graha SMKN 9                  ] |
| Penyelenggara     : [ Pengurus OSIS SMKN 9 Tangerang                          ] |
|                                                                                 |
| Unggah Poster     : +---------------------------------------------------------+ |
|                     | [Icon Upload] Drag & Drop Berkas Poster (JPG/PNG, Max 2MB)|
|                     | Berkas terpilih: poster_class_meeting.jpg (1.1 MB) [Hapus]|
|                     +---------------------------------------------------------+ |
| Deskripsi Lengkap : [ B | I | U | Link | Bullet ]                               |
|                     | Kegiatan kompetisi olahraga dan sportivitas antar rombel  |
|                     | tingkat 10 s/d 12 dalam rangka jeda semester ganjil...    |
+---------------------------------------------------------------------------------+
| [MODERASI KESISWAAN]                                                            |
| Status Saat Ini: [ MENUNGGU PERSETUJUAN (PENDING) ]                            |
| Catatan Revisi : [ Tuliskan catatan jika perlu perbaikan oleh OSIS...         ] |
|                                                                                 |
| [Tolak & Minta Revisi]            [Simpan Draf]       [SETUJUI & PUBLIKASIKAN]  |
+---------------------------------------------------------------------------------+
```

---

### Halaman 13: Halaman Pengaturan Akun & Keamanan
- **Hak Akses:** Semua Role (Profil Masing-masing).
- **Komponen:**
  - Ubah Kontak Pribadi (No. Telepon & WhatsApp).
  - Form Ganti Kata Sandi: Password Lama, Password Baru (dengan indikator kekuatan sandi), Konfirmasi Password Baru.
  - Riwayat Sesi Aktif: Tanggal login terakhir, browser yang dipakai, dan IP Address.

#### Wireframe Teks:
```
+---------------------------------------------------------------------------------+
| PENGATURAN AKUN SAYA                                                            |
+----------------------------------------+----------------------------------------+
| DATA PROFIL PENGGUNA                   | KEAMANAN & GANTI KATA SANDI            |
| Nama     : Ahmad Fauzi, S.Pd.          | Kata Sandi Saat Ini : [ ********** ]   |
| Role     : Wali Kelas (10 DKV 1)       | Kata Sandi Baru     : [ ********** ]   |
| Email    : ahmad.fauzi@smkn9tangerang  | Kekuatan: [ Sangat Kuat (8+ Karakter) ]|
| No. WA   : [ 081290001003            ] | Ulangi Sandi Baru   : [ ********** ]   |
|                                        |                                        |
| [Simpan Perubahan Kontak]              | [Perbarui Kata Sandi]                  |
+----------------------------------------+----------------------------------------+
| RIWAYAT AKTIVITAS & SESI LOGIN TERAKHIR                                         |
| 1. Sesi Aktif Saat Ini : Chrome di Windows 10 (IP: 192.168.1.15) - Hari ini    |
| 2. Sesi Lalu           : Mobile Safari di iPhone (IP: 182.253.10.4) - Kemarin   |
+---------------------------------------------------------------------------------+
```

