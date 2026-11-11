<?php
/**
 * Konfigurasi Utama Aplikasi SIAKAD SMKN 9 Tangerang
 */

defined('AKSES_SIAKAD') or define('AKSES_SIAKAD', true);

// Deteksi Base URL otomatis
$protokol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = rtrim($protokol . $host . $scriptName, '/');

// Jika dijalankan dari folder web-komet/public atau virtual host
define('APP_NAME', 'SIAKAD SMKN 9 Tangerang');
define('APP_ENV', 'development'); // 'development' atau 'production'
define('BASE_URL', $baseUrl);

define('PATH_ROOT', dirname(__DIR__, 2));
define('PATH_APP', PATH_ROOT . '/app');
define('PATH_VIEWS', PATH_ROOT . '/views');
define('PATH_PUBLIC', PATH_ROOT . '/public');
define('PATH_UPLOADS', PATH_PUBLIC . '/uploads');
define('PATH_LOGS', PATH_ROOT . '/logs');

// Manajemen Error
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
}
ini_set('log_errors', '1');
ini_set('error_log', PATH_LOGS . '/app_errors.log');

// Konfigurasi Keamanan Sesi
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    
    // Aktifkan secure cookie hanya jika HTTPS
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}

// Inactive session timeout (120 menit)
$timeoutSesi = 120 * 60;
if (isset($_SESSION['waktu_aktivitas_terakhir']) && (time() - $_SESSION['waktu_aktivitas_terakhir'] > $timeoutSesi)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['waktu_aktivitas_terakhir'] = time();

