<?php
/**
 * Middleware Proteksi Cross-Site Request Forgery (CSRF)
 */

namespace App\Middleware;

use App\Helpers\Session;

class CsrfMiddleware {
    public function handle(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

            if (!Session::validateCsrf($token)) {
                http_response_code(419);
                if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Sesi kadaluarsa atau token CSRF tidak valid. Silakan refresh halaman.']);
                    exit;
                }

                Session::setFlash('error', 'Sesi Anda telah berakhir atau formulir tidak valid. Silakan coba lagi.');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/login')));
                exit;
            }
        }
    }
}

