<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected $token;

    public function __construct()
    {
        $this->token = env('FONNTE_TOKEN');
    }

    /**
     * Fungsi utama untuk mengirim pesan WA.
     *
     * @param string $target Nomor HP tujuan (bisa 08... atau 628...)
     * @param string $message Isi pesan
     * @return array
     */
    public function sendMessage($target, $message)
    {
        // 1. Cek apakah token sudah di-set
        if (!$this->token) {
            Log::error('FONNTE_TOKEN belum di-set di .env');
            return ['status' => false, 'message' => 'Token Fonnte not configured.'];
        }

        try {
            // 2. Tembak API Fonnte
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62', // Fonnte akan otomatis format 08... jadi 628...
            ]);

            // 3. Catat ke Log (Bagus untuk debug)
            $responseData = $response->json();
            Log::info('Fonnte message sent to ' . $target, $responseData);

            // 4. Cek apakah response berhasil atau error
            if ($response->successful()) {
                // Cek format response Fonnte (bisa berbeda-beda)
                if (isset($responseData['status']) && $responseData['status'] === 'success') {
                    return ['status' => true, 'message' => 'Pesan berhasil dikirim', 'data' => $responseData];
                } elseif (isset($responseData['status']) && $responseData['status'] === false) {
                    // Handle error dengan reason yang lebih spesifik
                    $reason = $responseData['reason'] ?? null;
                    $errorMsg = $responseData['message'] ?? $responseData['msg'] ?? 'Gagal mengirim pesan';

                    // Pesan khusus untuk invalid token
                    if ($reason === 'invalid token' || str_contains(strtolower($errorMsg), 'invalid token') || str_contains(strtolower($errorMsg), 'token')) {
                        $errorMsg = 'Token Fonnte tidak valid. Silakan periksa FONNTE_TOKEN di file .env dan pastikan token aktif di dashboard Fonnte.';
                    }

                    return ['status' => false, 'message' => $errorMsg, 'reason' => $reason, 'data' => $responseData];
                } else {
                    // Format response tidak standar, anggap berhasil jika HTTP 200
                    return ['status' => true, 'message' => 'Pesan dikirim (response tidak standar)', 'data' => $responseData];
                }
            } else {
                // HTTP error (4xx, 5xx)
                $reason = $responseData['reason'] ?? null;
                $errorMsg = $responseData['message'] ?? $responseData['msg'] ?? 'HTTP Error: ' . $response->status();

                // Pesan khusus untuk invalid token
                if ($reason === 'invalid token' || str_contains(strtolower($errorMsg), 'invalid token') || str_contains(strtolower($errorMsg), 'token')) {
                    $errorMsg = 'Token Fonnte tidak valid. Silakan periksa FONNTE_TOKEN di file .env dan pastikan token aktif di dashboard Fonnte.';
                }

                return ['status' => false, 'message' => $errorMsg, 'reason' => $reason, 'data' => $responseData, 'http_status' => $response->status()];
            }

        } catch (\Exception $e) {
            // 5. Tangkap error jika API Fonnte down
            Log::error('Fonnte send error: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
