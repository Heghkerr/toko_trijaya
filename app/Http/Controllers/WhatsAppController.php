<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatbotService;
use App\Services\WhatsappService;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
            // IMPORTANT: Gunakan !== null karena "0" adalah falsy tapi valid message!
            if ($message !== null && $message !== '' && $phone !== null && $phone !== '') {

                $formattedPhone = $this->formatPhone($phone);
                $isRegisteredCustomer = Customer::where('phone', $formattedPhone)->exists();

                if (!$isRegisteredCustomer) {
                    Log::info('Message from unregistered customer - ignoring', [
                        'phone' => $phone,
                        'formatted_phone' => $formattedPhone,
                        'message' => substr($message, 0, 50)
                    ]);

                    return response()->json([
                        'status' => 'ignored',
                        'message' => 'Customer not registered'
                    ], 200);
                }

                Log::info('Customer verified - processing message', [
                    'phone' => $formattedPhone,
                    'message' => substr($message, 0, 50)
                ]);

                // ======================================================
                // DUPLICATE MESSAGE DETECTION
                // ======================================================

                // ======================================================
                // DUPLICATE DETECTION RULES
                // ======================================================

                $normalizedMessage = strtolower(trim($message));

                // Rule 1: Angka 0-9 (menu commands) - SKIP duplicate check
                $isNumericCommand = is_numeric($normalizedMessage) && strlen($normalizedMessage) <= 2;

                // Rule 2: Command keywords pendek - SKIP duplicate check
                $simpleCommands = ['menu', 'katalog', 'stok', 'pesan', 'batal', 'cek'];
                $isSimpleCommand = in_array($normalizedMessage, $simpleCommands);

                // Rule 3: Message pendek (< 10 karakter) - SKIP duplicate check
                // Karena biasanya command, bukan pesanan
                $isShortMessage = strlen($normalizedMessage) < 10;

                // Gabungkan rules
                $skipDuplicateCheck = $isNumericCommand || $isSimpleCommand || $isShortMessage;

                if ($skipDuplicateCheck) {
                    Log::info('Short command/message detected - skip duplicate check', [
                        'phone' => $phone,
                        'message' => $normalizedMessage,
                        'is_numeric' => $isNumericCommand,
                        'is_simple_command' => $isSimpleCommand,
                        'is_short' => $isShortMessage
                    ]);
                }

                // Hanya apply duplicate detection untuk LONG messages (pesanan, dll)
                if (!$skipDuplicateCheck) {
                    // Buat unique key berdasarkan phone + message content
                    $messageHash = md5($phone . '|' . trim($message));
                    $cacheKey = 'whatsapp_msg_' . $messageHash;

                    // Check jika message_id ada dari webhook (lebih akurat)
                    $messageId = $data['id'] ?? $data['message_id'] ?? null;
                    if ($messageId) {
                        $cacheKey = 'whatsapp_msg_id_' . $messageId;
                    }

                    // Cek apakah message sudah diproses dalam 5 menit terakhir
                    if (Cache::has($cacheKey)) {
                        Log::info('Duplicate message detected - skipping', [
                            'phone' => $phone,
                            'message' => substr($message, 0, 50),
                            'cache_key' => $cacheKey
                        ]);

                        return response()->json([
                            'status' => 'duplicate',
                            'message' => 'Message already processed'
                        ], 200);
                    }

                    // Tandai message sudah diproses (simpan di cache untuk 5 menit)
                    Cache::put($cacheKey, true, now()->addMinutes(5));
                }

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
            if (($message === null || $message === '') && ($phone === null || $phone === '') && ($data['state'] ?? false)) {
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

    /**
     * Format nomor telepon untuk konsistensi dengan database
     */
    protected function formatPhone($phone)
    {
        // Hapus karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Jika dimulai dengan 0, ganti dengan 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // Jika tidak dimulai dengan 62, tambahkan
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
