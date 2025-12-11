<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatbotService;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected $chatbotService;
    protected $whatsappService;

    public function __construct(ChatbotService $chatbotService, WhatsappService $whatsappService)
    {
        $this->chatbotService = $chatbotService;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Webhook untuk menerima pesan dari WhatsApp
     */
    public function webhook(Request $request)
    {
        try {
            // Log request untuk debugging
            Log::info('WhatsApp Webhook received', $request->all());

            // Format response dari Fonnte (bisa berbeda tergantung konfigurasi)
            $data = $request->all();

            // Pastikan chatbot hanya merespons jika pesan ditujukan untuk nomor tertentu
            $allowedReceiver = config('services.chatbot.receiver_phone', env('CHATBOT_RECEIVER_PHONE', '085931324084'));
            if ($allowedReceiver) {
                $receiver = $data['target'] ?? $data['to'] ?? $data['receiver'] ?? $data['device'] ?? null;
                if ($receiver) {
                    $normalizedReceiver = $this->normalizePhoneNumber($receiver);
                    $normalizedAllowed = $this->normalizePhoneNumber($allowedReceiver);

                    if ($normalizedAllowed && $normalizedReceiver && $normalizedAllowed !== $normalizedReceiver) {
                        Log::info('Webhook diabaikan: nomor tujuan tidak cocok', [
                            'receiver' => $receiver,
                            'allowed' => $allowedReceiver,
                        ]);
                        return response()->json(['status' => 'ignored'], 200);
                    }
                }
            }

            // Cek berbagai format yang mungkin dari Fonnte
            $phone = $data['phone'] ?? $data['from'] ?? $data['number'] ?? $data['sender'] ?? $data['pengirim'] ?? null;
            $message = $data['message'] ?? $data['text'] ?? $data['body'] ?? $data['pesan'] ?? null;

            // Log untuk debugging
            Log::info('Extracted phone and message', [
                'phone' => $phone,
                'message' => $message,
            ]);

            // Cek apakah ini pesan masuk
            if ($message && $phone) {
                // Proses pesan dengan chatbot
                Log::info('Processing message with chatbot', ['phone' => $phone, 'message' => $message]);
                $response = $this->chatbotService->processMessage($phone, $message);
                Log::info('Chatbot response', ['response' => $response]);

                return response()->json([
                    'status' => 'success',
                    'response' => $response,
                ], 200);
            }

            // Jika webhook adalah status delivery/read (state update) tanpa body pesan, jangan log warning
            if (!$message && !$phone && ($data['state'] ?? false)) {
                Log::info('Webhook status update received (no message/phone)', [
                    'state' => $data['state'] ?? null,
                    'id' => $data['id'] ?? null,
                    'stateid' => $data['stateid'] ?? null,
                ]);
                return response()->json(['status' => 'ok'], 200);
            }

            // Jika bukan pesan masuk, return success (untuk verifikasi webhook)
            Log::warning('Webhook received but phone or message is missing', [
                'phone' => $phone,
                'message' => $message,
            ]);
            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('WhatsApp Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Normalisasi nomor telepon (hapus non-digit dan awalan 0/62) agar mudah dibandingkan.
     */
    protected function normalizePhoneNumber(?string $number): ?string
    {
        if (!$number) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $number);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            $digits = substr($digits, 2);
        } else {
            $digits = ltrim($digits, '0');
        }

        return $digits ?: null;
    }
}
