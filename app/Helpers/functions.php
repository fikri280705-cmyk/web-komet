<?php
/**
 * Helper Fungsi Global (Tanpa Namespace) untuk View SIAKAD
 */

use App\Helpers\Sanitizer;
use App\Helpers\Session;

if (!function_exists('e')) {
    function e(?string $str): string {
        return Sanitizer::e($str);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return Session::csrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return Session::csrfField();
    }
}

