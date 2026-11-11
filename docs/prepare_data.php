<?php
/**
 * Script Pembuat SQL DDL & Dummy Data SMKN 9 Kabupaten Tangerang
 */

$jurusanList = [
    ['id' => 1, 'kode' => 'TKJ',  'nama' => 'Teknik Komputer Jaringan', 'kaprog_guru_id' => 4, 'lab' => 'Labkom 001', 'labs' => ['Labkom 001', 'Labkom 002', 'Labkom 003'], 'deskripsi' => 'Penguasaan instalasi jaringan, administrasi server, fiber optic, dan cybersecurity.'],
    ['id' => 2, 'kode' => 'DKV',  'nama' => 'Desain Komunikasi Visual', 'kaprog_guru_id' => 3, 'lab' => 'Lab Desain 001', 'labs' => ['Lab Desain 001', 'Lab Desain 002', 'Lab Desain 003'], 'deskripsi' => 'Pengembangan desain grafis, ilustrasi digital, UI/UX, dan videografi kreatif.'],
    ['id' => 3, 'kode' => 'AKC',  'nama' => 'Asisten Perawatan dan Caregiver', 'kaprog_guru_id' => 5, 'lab' => 'Lab Keperawatan 001', 'labs' => ['Lab Keperawatan 001', 'Lab Keperawatan 002', 'Lab Keperawatan 003'], 'deskripsi' => 'Pelayanan dasar keperawatan, homecare, pendampingan lansia, dan sanitasi kesehatan.'],
    ['id' => 4, 'kode' => 'FKK',  'nama' => 'Farmasi Klinik dan Komunitas', 'kaprog_guru_id' => 6, 'lab' => 'Lab Farmasi 001', 'labs' => ['Lab Farmasi 001', 'Lab Farmasi 002', 'Lab Farmasi 003'], 'deskripsi' => 'Teknik peracikan obat, farmakologi dasar, manajemen apotek, dan dispensing obat.'],
    ['id' => 5, 'kode' => 'MPLB', 'nama' => 'Manajemen Perkantoran dan Layanan Bisnis', 'kaprog_guru_id' => 7, 'lab' => null, 'labs' => [], 'deskripsi' => 'Pengelolaan administrasi modern, kearsipan digital, customer service, dan korespondensi bisnis.'],
    ['id' => 6, 'kode' => 'TBSM', 'nama' => 'Teknik dan Bisnis Sepeda Motor', 'kaprog_guru_id' => 8, 'lab' => 'Bengkel 001', 'labs' => ['Bengkel 001', 'Bengkel 002', 'Bengkel 003'], 'deskripsi' => 'Perawatan mesin berkala, kelistrikan sepeda motor, injeksi EFI, dan wirausaha bengkel.'],
];

// Kelas (36 kelas: 2 rombel per tingkat untuk 6 jurusan)
$kelasList = [];
$classId = 1;
foreach ($jurusanList as $j) {
    foreach ([10, 11, 12] as $tingkatIndex => $tingkat) {
        foreach ([1, 2] as $rombel) {
            $kelasList[] = [
                'id' => $classId++,
                'id_jurusan' => $j['id'],
                'tingkat' => $tingkat,
                'nomor_rombel' => $rombel,
                'nama_kelas' => "{$tingkat} {$j['kode']} {$rombel}",
                'wali_guru_id' => $rombel === 1
                    ? 9 + (($j['id'] - 1) * 3) + $tingkatIndex
                    : 41 + (($j['id'] - 1) * 3) + $tingkatIndex
            ];
        }
    }
}

// 40 Guru awal: 18 wali kelas dan 22 guru pengajar
$guruList = [
    // 2 Kesiswaan
    ['id' => 1, 'nuptk' => '197508142000031001', 'nip' => '197508142000031001', 'nama' => 'Drs. Hendra Permana, M.Pd.', 'jk' => 'L', 'peran_id' => 1, 'kode_peran' => 'kesiswaan', 'jurusan_id' => null, 'mapel' => 'Bahasa Indonesia'],
    ['id' => 2, 'nuptk' => '197803152002122002', 'nip' => '197803152002122002', 'nama' => 'Dra. Hj. Ratna Sari, M.Pd.', 'jk' => 'P', 'peran_id' => 1, 'kode_peran' => 'kesiswaan', 'jurusan_id' => null, 'mapel' => 'Pendidikan Pancasila'],
    
    // 6 Kaprog
    ['id' => 3, 'nuptk' => '198203202008012003', 'nip' => '198203202008012003', 'nama' => 'Siti Rahmawati, S.Sn.', 'jk' => 'P', 'peran_id' => 2, 'kode_peran' => 'kaprog', 'jurusan_id' => 2, 'mapel' => 'Dasar Desain Grafis & Nirmana'],
    ['id' => 4, 'nuptk' => '198005122006041004', 'nip' => '198005122006041004', 'nama' => 'Budi Santoso, S.Kom., M.T.', 'jk' => 'L', 'peran_id' => 2, 'kode_peran' => 'kaprog', 'jurusan_id' => 1, 'mapel' => 'Administrasi Infrastruktur Jaringan'],
    ['id' => 5, 'nuptk' => '198407182009022005', 'nip' => '198407182009022005', 'nama' => 'Ns. Dewi Anggraini, S.Kep.', 'jk' => 'P', 'peran_id' => 2, 'kode_peran' => 'kaprog', 'jurusan_id' => 3, 'mapel' => 'Kebutuhan Dasar Manusia'],
    ['id' => 6, 'nuptk' => '198609252010011006', 'nip' => '198609252010011006', 'nama' => 'apt. Rian Hidayat, S.Farm.', 'jk' => 'L', 'peran_id' => 2, 'kode_peran' => 'kaprog', 'jurusan_id' => 4, 'mapel' => 'Dasar Farmakologi & Peracikan'],
    ['id' => 7, 'nuptk' => '198302142008032007', 'nip' => '198302142008032007', 'nama' => 'Maya Sartika, S.E., M.M.', 'jk' => 'P', 'peran_id' => 2, 'kode_peran' => 'kaprog', 'jurusan_id' => 5, 'mapel' => 'Kearsipan Digital & Otomasi Kantor'],
    ['id' => 8, 'nuptk' => '198111052007011008', 'nip' => '198111052007011008', 'nama' => 'Ir. Joko Prasetyo, S.T.', 'jk' => 'L', 'peran_id' => 2, 'kode_peran' => 'kaprog', 'jurusan_id' => 6, 'mapel' => 'Pemeliharaan Mesin Sepeda Motor'],

    // 18 Wali Kelas (Guru 9 - 26), masing-masing untuk dua rombel paralel
    ['id' => 9,  'nuptk' => '199011152019031009', 'nip' => '199011152019031009', 'nama' => 'Ahmad Fauzi, S.Pd.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 1, 'kelas_id' => 1, 'mapel' => 'Jaringan Nirkabel & Fiber Optic'],
    ['id' => 10, 'nuptk' => '199104122020012010', 'nip' => '199104122020012010', 'nama' => 'Linda Wahyuni, S.Pd.', 'jk' => 'P', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 1, 'kelas_id' => 3, 'mapel' => 'Sistem Komputer & Cloud'],
    ['id' => 11, 'nuptk' => '198906232018021011', 'nip' => '198906232018021011', 'nama' => 'Eko Kurniawan, S.Pd.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 1, 'kelas_id' => 5, 'mapel' => 'Cybersecurity & Routing'],
    
    ['id' => 12, 'nuptk' => '199208302021032012', 'nip' => '199208302021032012', 'nama' => 'Rina Marlina, S.Pd.', 'jk' => 'P', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 2, 'kelas_id' => 7, 'mapel' => 'Tipografi & Ilustrasi Digital'],
    ['id' => 13, 'nuptk' => '198712102014041013', 'nip' => '198712102014041013', 'nama' => 'Aris Munandar, S.Sn.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 2, 'kelas_id' => 9, 'mapel' => 'Fotografi & Videografi'],
    ['id' => 14, 'nuptk' => '199301152022012014', 'nip' => '199301152022012014', 'nama' => 'Dian Permata, S.Pd.', 'jk' => 'P', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 2, 'kelas_id' => 11, 'mapel' => 'Desain Publikasi & 3D Modeling'],
    
    ['id' => 15, 'nuptk' => '199003252019011015', 'nip' => '199003252019011015', 'nama' => 'Ns. Farhan Hakim, S.Kep.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 3, 'kelas_id' => 13, 'mapel' => 'Konsep Dasar Keperawatan'],
    ['id' => 16, 'nuptk' => '199107142020022016', 'nip' => '199107142020022016', 'nama' => 'Siti Aminah, S.Kep.', 'jk' => 'P', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 3, 'kelas_id' => 15, 'mapel' => 'Komunikasi Keperawatan'],
    ['id' => 17, 'nuptk' => '198810202016032017', 'nip' => '198810202016032017', 'nama' => 'Nurul Hidayah, S.Kep.', 'jk' => 'P', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 3, 'kelas_id' => 17, 'mapel' => 'Perawatan Lansia & Caregiver'],

    ['id' => 18, 'nuptk' => '199205182021021018', 'nip' => '199205182021021018', 'nama' => 'apt. Wahyu Pratama, S.Farm.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 4, 'kelas_id' => 19, 'mapel' => 'Pelayanan Farmasi & Resep'],
    ['id' => 19, 'nuptk' => '199309102022032019', 'nip' => '199309102022032019', 'nama' => 'apt. Anisa Rahma, S.Farm.', 'jk' => 'P', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 4, 'kelas_id' => 21, 'mapel' => 'Farmakognosi Bahan Alam'],
    ['id' => 20, 'nuptk' => '198911022017011020', 'nip' => '198911022017011020', 'nama' => 'apt. Bagus Triadi, S.Farm.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 4, 'kelas_id' => 23, 'mapel' => 'Kimia Farmasi Terapan'],

    ['id' => 21, 'nuptk' => '199002162019022021', 'nip' => '199002162019022021', 'nama' => 'Sri Mulyani, S.Pd.', 'jk' => 'P', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 5, 'kelas_id' => 25, 'mapel' => 'Korespondensi & Humas'],
    ['id' => 22, 'nuptk' => '198804222015031022', 'nip' => '198804222015031022', 'nama' => 'Dedi Supriadi, S.Pd.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 5, 'kelas_id' => 27, 'mapel' => 'Pengelolaan Kas Kecil & Keuangan'],
    ['id' => 23, 'nuptk' => '199206302021012023', 'nip' => '199206302021012023', 'nama' => 'Endang Wulandari, S.Pd.', 'jk' => 'P', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 5, 'kelas_id' => 29, 'mapel' => 'Manajemen Logistik & Rapat'],

    ['id' => 24, 'nuptk' => '198709142014021024', 'nip' => '198709142014021024', 'nama' => 'Agus Setiawan, S.T.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 6, 'kelas_id' => 31, 'mapel' => 'Pemeliharaan Sasis Sepeda Motor'],
    ['id' => 25, 'nuptk' => '198901282018011025', 'nip' => '198901282018011025', 'nama' => 'Bambang Irawan, S.T.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 6, 'kelas_id' => 33, 'mapel' => 'Kelistrikan Sepeda Motor & EFI'],
    ['id' => 26, 'nuptk' => '199103052020041026', 'nip' => '199103052020041026', 'nama' => 'Hendri Gunawan, S.Pd.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'wali_kelas', 'jurusan_id' => 6, 'kelas_id' => 35, 'mapel' => 'Pengelolaan Bengkel Sepeda Motor'],

    // 14 Guru Pengajar Mapel Umum (Guru 27 - 40)
    ['id' => 27, 'nuptk' => '197604102001011027', 'nip' => '197604102001011027', 'nama' => 'H. Syamsudin, S.Ag.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Pendidikan Agama Islam'],
    ['id' => 28, 'nuptk' => '198207152008021028', 'nip' => '198207152008021028', 'nama' => 'Yohanes Maryanto, S.Pd.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Pendidikan Agama Kristen'],
    ['id' => 29, 'nuptk' => '198503112009031029', 'nip' => '198503112009031029', 'nama' => 'Rahmat Hidayat, S.Pd.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Bahasa Indonesia'],
    ['id' => 30, 'nuptk' => '199009182018022030', 'nip' => '199009182018022030', 'nama' => 'Fitri Handayani, S.Pd.', 'jk' => 'P', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Bahasa Inggris'],
    ['id' => 31, 'nuptk' => '198711202015011031', 'nip' => '198711202015011031', 'nama' => 'Deni Saputra, S.Pd.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Matematika Terapan'],
    ['id' => 32, 'nuptk' => '199101052020032032', 'nip' => '199101052020032032', 'nama' => 'Yulia Anggraini, S.Pd.', 'jk' => 'P', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Matematika Terapan'],
    ['id' => 33, 'nuptk' => '198606142012041033', 'nip' => '198606142012041033', 'nama' => 'Taufik Hidayat, S.Pd.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Pendidikan Jasmani & Olahraga'],
    ['id' => 34, 'nuptk' => '198808292016021034', 'nip' => '198808292016021034', 'nama' => 'Roni Wijaya, S.Pd.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Sejarah Indonesia'],
    ['id' => 35, 'nuptk' => '199304122022012035', 'nip' => '199304122022012035', 'nama' => 'Nina Marlina, S.Pd.', 'jk' => 'P', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Seni Budaya'],
    ['id' => 36, 'nuptk' => '198912012017031036', 'nip' => '198912012017031036', 'nama' => 'Herman Susilo, S.Kom.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Informatika'],
    ['id' => 37, 'nuptk' => '198402192009012037', 'nip' => '198402192009012037', 'nama' => 'Lilis Suryani, S.Pd.', 'jk' => 'P', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Projek IPAS'],
    ['id' => 38, 'nuptk' => '198510252011021038', 'nip' => '198510252011021038', 'nama' => 'Tri Wahyudi, S.Pd.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Pendidikan Pancasila'],
    ['id' => 39, 'nuptk' => '199007082019012039', 'nip' => '199007082019012039', 'nama' => 'Nur Hasanah, S.Pd.', 'jk' => 'P', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Bimbingan Konseling (BK)'],
    ['id' => 40, 'nuptk' => '199211142021021040', 'nip' => '199211142021021040', 'nama' => 'Arif Wicaksono, S.T.', 'jk' => 'L', 'peran_id' => 3, 'kode_peran' => 'guru_pengajar', 'jurusan_id' => null, 'mapel' => 'Dasar-dasar Teknik & K3LH'],
];

// 18 wali kelas tambahan agar setiap kelas memiliki wali yang berbeda
$waliKelasNames = [
    'Rizal Maulana, S.Pd.',
    'Desi Puspitasari, S.Pd.',
    'Fajar Adinugroho, S.Pd.',
    'Novi Handayani, S.Pd.',
    'Feriansyah Putra, S.Pd.',
    'Putri Amelia, S.Pd.',
    'Rendi Kurnia, S.Pd.',
    'Diah Lestari, S.Pd.',
    'Raka Pratama, S.Pd.',
    'Anisa Fitriani, S.Pd.',
    'Iqbal Fauzi, S.Pd.',
    'Riska Wulandari, S.Pd.',
    'Naufal Akbar, S.Pd.',
    'Mega Purnamasari, S.Pd.',
    'Dicky Firmansyah, S.Pd.',
    'Sari Novitasari, S.Pd.',
    'Hendra Saputro, S.Pd.',
    'Intan Permatasari, S.Pd.'
];
$waliMapelByJurusan = [
    1 => ['Dasar-dasar Teknik Jaringan Komputer', 'Administrasi Infrastruktur Jaringan', 'Cybersecurity & Keamanan Jaringan'],
    2 => ['Dasar Desain Grafis & Nirmana', 'Tipografi & Ilustrasi Digital', 'Fotografi & Videografi Kejuruan'],
    3 => ['Konsep Dasar Keperawatan & Anatomi', 'Kebutuhan Dasar Manusia & Caregiver', 'Keterampilan Dasar Tindakan Keperawatan'],
    4 => ['Dasar Farmakologi & Peracikan Resep', 'Farmakognosi & Bahan Alam Medis', 'Pelayanan Farmasi & Manajemen Obat'],
    5 => ['Korespondensi Bisnis & Kearsipan', 'Komunikasi Humas & Protokoler', 'Otomasi Tata Kelola Perkantoran'],
    6 => ['Pemeliharaan Mesin Sepeda Motor', 'Pemeliharaan Sasis Sepeda Motor', 'Kelistrikan Sepeda Motor & Sistem Injeksi']
];

foreach ($waliKelasNames as $index => $nama) {
    $guruId = 41 + $index;
    $jurusanId = intdiv($index, 3) + 1;
    $nuptk = '19980101202002' . sprintf('%04d', $guruId);

    $guruList[] = [
        'id' => $guruId,
        'nuptk' => $nuptk,
        'nip' => $nuptk,
        'nama' => $nama,
        'jk' => $index % 2 === 0 ? 'L' : 'P',
        'peran_id' => 3,
        'kode_peran' => 'wali_kelas',
        'jurusan_id' => $jurusanId,
        'mapel' => $waliMapelByJurusan[$jurusanId][$index % 3]
    ];
}

// 40 Guru Pengajar tambahan dengan nama fiktif
$guruPengajarNames = [
    'Agung Setyawan, S.Pd.',
    'Asep Nugroho, S.Pd.',
    'Cahyo Prabowo, S.Pd.',
    'Darma Putra, S.Pd.',
    'Erlangga Saputra, S.Pd.',
    'Fadli Ramadhan, S.Pd.',
    'Galih Prakoso, S.Pd.',
    'Hafiz Firmansyah, S.Pd.',
    'Imam Maulana, S.Pd.',
    'Jefri Kurniawan, S.Pd.',
    'Krisna Wicaksono, S.Pd.',
    'Lukman Hakim, S.Pd.',
    'Miftah Fauzi, S.Pd.',
    'Nanda Permadi, S.Pd.',
    'Oki Setiawan, S.Pd.',
    'Prasetya Adinata, S.Pd.',
    'Qomaruddin Hidayat, S.Pd.',
    'Rifqi Alamsyah, S.Pd.',
    'Surya Pamungkas, S.Pd.',
    'Teguh Wibisana, S.Pd.',
    'Umar Faruq, S.Pd.',
    'Vina Oktaviani, S.Pd.',
    'Wulan Pertiwi, S.Pd.',
    'Yusuf Hidayat, S.Pd.',
    'Zaki Firmansyah, S.Pd.',
    'Adnan Firmansyah, S.Pd.',
    'Bima Arya, S.Pd.',
    'Citra Anjani, S.Pd.',
    'Della Maharani, S.Pd.',
    'Eka Pratiwi, S.Pd.',
    'Faisal Akbar, S.Pd.',
    'Hana Kusumawati, S.Pd.',
    'Indra Permana, S.Pd.',
    'Jihan Safitri, S.Pd.',
    'Laras Wulandari, S.Pd.',
    'Mawar Anggraini, S.Pd.',
    'Niko Saputra, S.Pd.',
    'Rafi Darmawan, S.Pd.',
    'Salsa Nirmala, S.Pd.',
    'Tania Kusuma, S.Pd.'
];
$mapelGuruPengajar = [
    'Pendidikan Agama dan Budi Pekerti',
    'Bahasa Indonesia',
    'Bahasa Inggris',
    'Matematika Terapan',
    'Pendidikan Jasmani & Olahraga',
    'Sejarah Indonesia',
    'Informatika & Literasi Digital',
    'Pendidikan Pancasila',
    'Seni Budaya & Kreativitas',
    'Projek IPAS Terapan'
];

foreach ($guruPengajarNames as $index => $nama) {
    $guruId = 59 + $index;
    $nuptk = '19980101202002' . sprintf('%04d', $guruId);

    $guruList[] = [
        'id' => $guruId,
        'nuptk' => $nuptk,
        'nip' => $nuptk,
        'nama' => $nama,
        'jk' => $index % 2 === 0 ? 'L' : 'P',
        'peran_id' => 3,
        'kode_peran' => 'guru_pengajar',
        'jurusan_id' => null,
        'mapel' => $mapelGuruPengajar[$index % count($mapelGuruPengajar)]
    ];
}

// 1.080 Siswa (36 kelas x 30 siswa)
$firstNamesL = ['Ahmad', 'Muhammad', 'Rizky', 'Dimas', 'Fikri', 'Bagus', 'Aditya', 'Bayu', 'Fajar', 'Kevin', 'Reza', 'Gilang', 'Ilham', 'Farhan', 'Rian', 'Danu', 'Aldi', 'Wahyu', 'Satria', 'Bagas'];
$firstNamesP = ['Amanda', 'Siti', 'Nayla', 'Aisyah', 'Putri', 'Nadia', 'Zahra', 'Indah', 'Lestari', 'Tiara', 'Salma', 'Dwi', 'Anisa', 'Rani', 'Salsabila', 'Fitri', 'Nurul', 'Tari', 'Maya', 'Gita'];
$middleNames = ['Aulia', 'Rafi', 'Naufal', 'Safira', 'Daffa', 'Azizah', 'Fathan', 'Nabila', 'Rizwan', 'Kirana', 'Fauzan', 'Humaira', 'Akbar', 'Syifa', 'Rangga', 'Amira', 'Rasyid', 'Nadira', 'Faris', 'Azzahra'];
$lastNames = ['Pratama', 'Lestari', 'Wibowo', 'Ramadhan', 'Saputra', 'Kusuma', 'Santoso', 'Utami', 'Siregar', 'Hidayat', 'Firmansyah', 'Mahendra', 'Permata', 'Nugraha', 'Wijaya', 'Suryani', 'Pertiwi', 'Anggraini', 'Syahrani', 'Gunawan'];

$siswaList = [];
$siswaId = 1;
$nisBase = 2024101000;

foreach ($kelasList as $k) {
    for ($i = 1; $i <= 30; $i++) {
        $nis = (string)($nisBase + $siswaId);
        $isL = ($i % 2 !== 0);
        $nameOffset = $siswaId - 1;
        $groupIndex = intdiv($nameOffset, 40);
        $positionInGroup = $nameOffset % 40;
        $firstNameList = $isL ? $firstNamesL : $firstNamesP;
        $fname = $firstNameList[intdiv($positionInGroup, 2)];
        $mname = $middleNames[$groupIndex % count($middleNames)];
        $lname = $lastNames[intdiv($groupIndex, count($middleNames)) % count($lastNames)];
        $fullName = "{$fname} {$mname} {$lname}";
        
        $tahunMasuk = $k['tingkat'] === 10 ? 2024 : ($k['tingkat'] === 11 ? 2023 : 2022);
        $birthYear = 2024 - $k['tingkat'] - 5; // e.g. 2008 for 10th grade
        
        $siswaList[] = [
            'id' => $siswaId,
            'nis' => $nis,
            'nisn' => '00' . sprintf('%08d', 10000000 + $siswaId),
            'nama' => $fullName,
            'jk' => $isL ? 'L' : 'P',
            'tempat_lahir' => 'Tangerang',
            'tanggal_lahir' => "{$birthYear}-" . sprintf('%02d', rand(1, 12)) . '-' . sprintf('%02d', rand(1, 28)),
            'id_jurusan' => $k['id_jurusan'],
            'id_kelas' => $k['id'],
            'tingkat' => $k['tingkat'],
            'tahun_masuk' => $tahunMasuk,
            'agama' => 'Islam',
            'alamat' => "Jl. Raya Serang Km. " . rand(10, 30) . ", Cikupa, Tangerang",
            'provinsi' => 'Banten',
            'kabupaten' => 'Kabupaten Tangerang',
            'kode_pos' => '15710',
            'telepon' => '08' . rand(11, 99) . rand(1000000, 9999999),
            'nama_ayah' => 'Bambang ' . $lname,
            'nama_ibu' => 'Sri ' . $lname,
            'pekerjaan_ortu' => 'Karyawan Swasta',
            'telepon_ortu' => '08' . rand(11, 99) . rand(1000000, 9999999),
        ];
        $siswaId++;
    }
}

// OSIS Selection (10 siswa dari 180)
$osisList = [
    ['id_siswa' => 12, 'jabatan' => 'Ketua OSIS', 'periode' => '2024/2025'],
    ['id_siswa' => 11, 'jabatan' => 'Wakil Ketua OSIS', 'periode' => '2024/2025'],
    ['id_siswa' => 21, 'jabatan' => 'Sekretaris 1', 'periode' => '2024/2025'],
    ['id_siswa' => 31, 'jabatan' => 'Sekretaris 2', 'periode' => '2024/2025'],
    ['id_siswa' => 41, 'jabatan' => 'Bendahara 1', 'periode' => '2024/2025'],
    ['id_siswa' => 51, 'jabatan' => 'Bendahara 2', 'periode' => '2024/2025'],
    ['id_siswa' => 1,  'jabatan' => 'Sekbid IPTEK & Informasi', 'periode' => '2024/2025'],
    ['id_siswa' => 61, 'jabatan' => 'Sekbid Olahraga & Kesehatan', 'periode' => '2024/2025'],
    ['id_siswa' => 71, 'jabatan' => 'Sekbid Seni & Budaya', 'periode' => '2024/2025'],
    ['id_siswa' => 81, 'jabatan' => 'Sekbid Kedisiplinan & Bela Negara', 'periode' => '2024/2025'],
    ['id_siswa' => 91, 'jabatan' => 'Anggota Divisi Humas', 'periode' => '2024/2025'],
    ['id_siswa' => 101, 'jabatan' => 'Anggota Divisi Kreatif', 'periode' => '2024/2025'],
];

file_put_contents(__DIR__ . '/dataset_prepared.json', json_encode([
    'jurusan' => $jurusanList,
    'kelas' => $kelasList,
    'guru' => $guruList,
    'siswa' => $siswaList,
    'osis' => $osisList
], JSON_PRETTY_PRINT));

echo "Dataset JSON prepared successfully!\n";
