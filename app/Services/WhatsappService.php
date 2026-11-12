<?php
/**
 * Pengirim WhatsApp ke nomor siswa (teks saja)
 */

namespace App\Services;

class WhatsappService {
    private array $config;

    public function __construct(?array $config = null) {
        $path = dirname(__DIR__) . '/Config/Whatsapp.php';
        $this->config = $config ?? (is_file($path) ? require $path : []);
    }

    public function normalisasiNomor(?string $nomor): ?string {
        if ($nomor === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $nomor);
        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        if (!preg_match('/^62[0-9]{8,15}$/', $digits)) {
            return null;
        }

        return $digits;
    }

    public function buatTautanWa(string $nomorInternasional, string $pesan): string {
        return 'https://wa.me/' . $nomorInternasional . '?text=' . rawurlencode($pesan);
    }

    /**
     * @return array{ok:bool,status:string,response:?string,tautan:?string,nomor:?string}
     */
    public function kirim(?string $nomorMentah, string $pesan): array {
        $nomor = $this->normalisasiNomor($nomorMentah);
        if ($nomor === null) {
            return [
                'ok' => false,
                'status' => 'nomor_tidak_valid',
                'response' => 'Nomor WhatsApp siswa tidak valid atau kosong.',
                'tautan' => null,
                'nomor' => null,
            ];
        }

        $tautan = $this->buatTautanWa($nomor, $pesan);
        $enabled = (bool)($this->config['enabled'] ?? true);
        $token = trim((string)($this->config['token'] ?? ''));
        $provider = (string)($this->config['provider'] ?? 'fonnte');

        if (!$enabled) {
            return [
                'ok' => false,
                'status' => 'nonaktif',
                'response' => 'Pengiriman WhatsApp dinonaktifkan.',
                'tautan' => $tautan,
                'nomor' => $nomor,
            ];
        }

        if ($token === '' || $provider === 'log') {
            return $this->kirimModeLokal($nomor, $pesan, $tautan);
        }

        return $this->kirimFonnte($nomor, $pesan, $tautan, $token);
    }

    private function kirimModeLokal(string $nomor, string $pesan, string $tautan): array {
        $baris = sprintf(
            "[%s] TO=%s STATUS=lokal_antrian LINK=%s MSG=%s\n",
            date('Y-m-d H:i:s'),
            $nomor,
            $tautan,
            str_replace(["\r", "\n"], ['', ' | '], $pesan)
        );

        $logDir = dirname(__DIR__, 2) . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }
        @file_put_contents($logDir . '/whatsapp.log', $baris, FILE_APPEND);

        return [
            'ok' => true,
            'status' => 'lokal_antrian',
            'response' => 'Mode lokal: pesan dicatat ke log WhatsApp.',
            'tautan' => $tautan,
            'nomor' => $nomor,
        ];
    }

    private function kirimFonnte(string $nomor, string $pesan, string $tautan, string $token): array {
        $apiUrl = (string)($this->config['api_url'] ?? 'https://api.fonnte.com/send');

        if (!function_exists('curl_init')) {
            return [
                'ok' => false,
                'status' => 'gagal',
                'response' => 'Ekstensi cURL PHP tidak tersedia untuk kirim WhatsApp.',
                'tautan' => $tautan,
                'nomor' => $nomor,
            ];
        }

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'target' => $nomor,
                'message' => $pesan,
                'countryCode' => '62',
            ],
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $token,
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $responseBody = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseBody === false) {
            $responseBody = curl_error($ch);
        }
        curl_close($ch);

        $ok = $httpCode >= 200 && $httpCode < 300;
        $decoded = json_decode((string)$responseBody, true);
        if (is_array($decoded)) {
            if (array_key_exists('status', $decoded)) {
                $ok = (bool)$decoded['status'] || $decoded['status'] === 'success';
            }
            if (array_key_exists('reason', $decoded) && !$ok) {
                $responseBody = (string)$decoded['reason'];
            }
        }

        return [
            'ok' => $ok,
            'status' => $ok ? 'terkirim' : 'gagal',
            'response' => is_string($responseBody) ? $responseBody : json_encode($responseBody),
            'tautan' => $tautan,
            'nomor' => $nomor,
        ];
    }
}
