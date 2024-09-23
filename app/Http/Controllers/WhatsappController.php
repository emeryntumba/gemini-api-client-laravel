<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class WhatsappController extends Controller
{
    public function receiveMessage(Request $request){
        $from = $request->input('From');
        $body = $request->input('Body');

        $responseFromAI = $this->sendToAIServer($body);

        $this->sendMessage($from, $responseFromAI);
    }

    private function sendToAIServer($message){
        return 'Réponse IA';
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
