<?php
/**
 * Model Jurusan (Konsentrasi Keahlian) & Model Kelas (Rombel)
 */

namespace App\Models;

use App\Core\Model;

class Jurusan extends Model {
    public function ambilSemua(): array {
        $sql = "SELECT j.*, g.nama_lengkap AS nama_kaprog 
                FROM jurusan j
                LEFT JOIN guru g ON j.id_guru_kaprog = g.id
                ORDER BY j.kode_jurusan ASC";
        return $this->fetchAll($sql);
    }

    public function cariBerdasarkanId(int $id): ?array {
        $sql = "SELECT j.*, g.nama_lengkap AS nama_kaprog 
                FROM jurusan j
                LEFT JOIN guru g ON j.id_guru_kaprog = g.id
                WHERE j.id = :id";
        return $this->fetchOne($sql, [':id' => $id]);
    }

    public function cariBerdasarkanIdPenggunaKaprog(int $idPengguna): ?array {
        $sql = "SELECT j.* 
                FROM jurusan j
                JOIN guru g ON j.id_guru_kaprog = g.id
                WHERE g.id_pengguna = :id_pengguna
                LIMIT 1";
        return $this->fetchOne($sql, [':id_pengguna' => $idPengguna]);
    }
}

