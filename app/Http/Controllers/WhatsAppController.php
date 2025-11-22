<!-- <?php

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

            // Cek berbagai format yang mungkin dari Fonnte
            $phone = $data['phone'] ?? $data['from'] ?? $data['number'] ?? null;
            $message = $data['message'] ?? $data['text'] ?? $data['body'] ?? null;

            // Cek apakah ini pesan masuk
            if ($message && $phone) {
                // Proses pesan dengan chatbot
                $response = $this->chatbotService->processMessage($phone, $message);

                return response()->json([
                    'status' => 'success',
                    'response' => $response
                ], 200);
            }

            // Jika bukan pesan masuk, return success (untuk verifikasi webhook)
            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('WhatsApp Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

}
