<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Http\Controllers\GeminiController;
use App\Services\GeminiService;

class WhatsappController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }
    public function receiveMessage(Request $request){
        $from = $request->input('From');
        $body = $request->input('Body');

        $responseFromAI = $this->sendToAIServer($body);

        $this->sendMessage($from, $responseFromAI);
    }

    private function sendToAIServer($message){
        $response = $this->geminiService->generateContent($message);
        $result = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return $result;
    }

    private function sendMessage($to, $message){
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');

        $twilio = new Client($sid, $token);
        $twilio->messages->create($to, [
            'from' => env('TWILIO_WHATSAPP_FROM'),
            'body' => $message,
        ]);
    }
}
