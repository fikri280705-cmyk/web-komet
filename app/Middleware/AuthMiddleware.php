<?php
/**
 * Middleware Pemeriksaan Otentikasi Login Pengguna
 */

namespace App\Middleware;

use App\Helpers\Session;

class AuthMiddleware {
    public function handle(): void {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Silakan masuk terlebih dahulu untuk mengakses sistem.');
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}

