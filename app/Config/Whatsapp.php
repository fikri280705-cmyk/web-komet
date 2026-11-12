<?php
/**
 * Konfigurasi pengiriman WhatsApp (Fonnte-compatible).
 * Isi token Fonnte agar pesan benar-benar terkirim ke nomor siswa.
 * Jika token kosong, sistem memakai mode lokal (log + tautan wa.me).
 */
return [
    'enabled'  => true,
    // 'fonnte' = API Fonnte, 'log' = lokal tanpa API
    'provider' => 'fonnte',
    'api_url'  => 'https://api.fonnte.com/send',
    // Token perangkat Fonnte
    'token'    => '',
    // jeda antar nomor (ms). Naikkan jika kena rate-limit Fonnte.
    'delay_ms' => 300,
];
