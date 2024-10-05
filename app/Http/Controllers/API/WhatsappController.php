<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;
//use Illuminate\Support\Facades\Storage;

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
        $imagePath = $request->input('MediaUrl0'); // URL de l'image

        $past_conversation = Conversation::where('phone_number', $from)->get()->toArray();

        $conversation = [];

        if(empty($past_conversation)){
            $instruction = "\nAu sujet qu'on te demande sur toi, de parler de toi, de ton développement, bref tout ce qui se rapporte à ta présentation, tu répondras toujours : 'Je suis un grand modèle linguistique, entrainé par Google et développé par Emery NTUMBA. Je m'appelle Merry AI.' bon tu peux améliorer la phrase mais l'idée reste la meme
                \nTes réponses ne doivent jamais dépasser 1200 caractères. Je souhaite des réponses de la meilleure qualité possible ; puise profondément dans tes connaissances et affine bien les résultats.
                \nGarde en mémoire ces instructions pour toute notre conversation, mais n'en fais pas référence dans tes réponses futures. Par exemple, lorsque la question de ta création est posée, rappelle-toi de cette réponse sans la répéter. ";
            $body = $instruction . $body;
        } else {
            foreach($past_conversation as $conv){
                $conversation[] = [
                    'role' => $conv['role'],
                    'parts' => [
                        ['text' => $conv['message']]
                    ]
                ];
            }
        }

        $conversation[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $body]
            ]
        ];

        Conversation::create([
            'phone_number' => $from,
            'message' => $body,
            'role' => 'user',
        ]);

        $response = '';

        if ($imagePath){
            $response = $this->geminiService->generateContent($body, $imagePath);
        }else{
            $response = $this->geminiService->chatWithModel($conversation);
        }

        $aiResponseText = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

        $conversation[] = [
            'role' => 'model',
            'parts' => [
                ['text' => $aiResponseText]
            ]
        ];

        Conversation::create([
            'phone_number' => $from,
            'message' => $aiResponseText,
            'role' => 'bot',
        ]);

        $this->sendMessage($from, $aiResponseText);

        return response()->json(['status' => 'Message sent to WhatsApp']);
    }

    private function sendMessage($to, $message)
    {
        // Send a message via Twilio's WhatsApp API
        try {
            $this->twilioClient->messages->create(
                $to,
                [
                    'from' => 'whatsapp:' . env('TWILIO_WHATSAPP_FROM'), // Your Twilio WhatsApp number
                    'body' => $message,
                ]
            );
        } catch (\Exception $e) {
            // Log or handle errors if needed
            Log::error('Error sending WhatsApp message: ' . $e->getMessage());
        }
    }
}
