<?php
/**
 * Helper Manajemen Sesi, CSRF Token, dan Flash Message
 */

namespace App\Helpers;

class Session {
    public static function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function isLoggedIn(): bool {
        return !empty($_SESSION['pengguna_id']);
    }

    public static function user(): ?array {
        if (!self::isLoggedIn()) return null;
        return [
            'id'            => $_SESSION['pengguna_id'],
            'nomor_induk'   => $_SESSION['nomor_induk'] ?? '',
            'nama_lengkap'  => $_SESSION['nama_lengkap'] ?? '',
            'email'         => $_SESSION['email'] ?? '',
            'id_peran'      => $_SESSION['id_peran'] ?? 0,
            'kode_peran'    => $_SESSION['kode_peran'] ?? '',
            'nama_peran'    => $_SESSION['nama_peran'] ?? '',
            'foto_profil'   => $_SESSION['foto_profil'] ?? 'avatar_default.png',
        ];
    }

    public static function role(): string {
        return $_SESSION['kode_peran'] ?? '';
    }

    /**
     * Flash Message (Pesan Notifikasi Sementara)
     */
    public static function setFlash(string $type, string $message): void {
        $_SESSION['flash'][$type] = $message;
    }

    public static function getFlash(string $type): ?string {
        if (isset($_SESSION['flash'][$type])) {
            $msg = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $msg;
        }
        return null;
    }

    public static function hasFlash(string $type): bool {
        return !empty($_SESSION['flash'][$type]);
    }

    /**
     * CSRF Token Generator & Validator
     */
    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string {
        $token = self::csrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validateCsrf(?string $token): bool {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
