<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller
{
    protected $geminiService;
    protected $twilioClient;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
        $this->twilioClient = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));  // Initialize Twilio client
    }

    public function receiveMessage(Request $request)
    {
        $from = $request->input('From');  // WhatsApp number of the sender
        $body = $request->input('Body');  // Message from the user

        // Process AI response
        $responseFromAI = $this->sendToAIServer($body);

        // Send the AI response back to the user via WhatsApp
        $this->sendMessage($from, $responseFromAI);

        return response()->json(['status' => 'Message sent to WhatsApp']);
    }

    private function sendToAIServer($message)
    {
        $response = $this->geminiService->generateContent($message);
        $result = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return $result;
    }

    private function sendMessage($to, $message)
    {
        // Send a message via Twilio's WhatsApp API
        try {
            $this->twilioClient->messages->create(
                $to,
                [
                    'from' => 'whatsapp:' . env('TWILIO_WHATSAPP_NUMBER'), // Your Twilio WhatsApp number
                    'body' => $message,
                ]
            );
        } catch (\Exception $e) {
            // Log or handle errors if needed
            Log::error('Error sending WhatsApp message: ' . $e->getMessage());
        }
    }
}
