<?php
/**
 * Middleware Pemeriksaan Otorisasi Hak Akses (Role-Based Access Control)
 */

namespace App\Middleware;

use App\Helpers\Session;

class RoleMiddleware {
    /**
     * Helper untuk membuat callback pengecekan peran spesifik
     */
    public static function izinkan(array $peranDiizinkan): callable {
        return function () use ($peranDiizinkan) {
            if (!Session::isLoggedIn()) {
                Session::setFlash('error', 'Silakan masuk terlebih dahulu.');
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

            $peranPengguna = Session::role();

            if (!in_array($peranPengguna, $peranDiizinkan, true)) {
                http_response_code(403);
                require PATH_VIEWS . '/errors/403.php';
                exit;
            }
        };
    }
}

