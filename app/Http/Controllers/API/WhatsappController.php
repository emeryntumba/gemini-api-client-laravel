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
        //$mediaType = $request->input('MediaContentType0'); // Type MIME

        if(!session()->has('instruction_sent')){
            $instruction = "\nÀ la question de savoir qui t'a créé, tu répondras toujours : 'Je suis développé par Emery et je m'appelle Merry AI.'
                \nTes réponses ne doivent jamais dépasser 1200 caractères. Je souhaite des réponses de la meilleure qualité possible ; puise profondément dans tes connaissances et affine bien les résultats.
                \nGarde en mémoire ces instructions pour toute notre conversation, mais n'en fais pas référence dans tes réponses futures. Par exemple, lorsque la question de ta création est posée, rappelle-toi de cette réponse sans la répéter. ";
            $body = $instruction . $body;
            session()->put('instruction_sent', true);
        }

        $conversation = session()->get('conversation', []);

        $conversation[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $body]
            ]
        ];

        Conversation::create([
            'session_id' => session()->getId(),
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
            'session_id' => session()->getId(),
            'message' => $aiResponseText,
            'role' => 'bot',
        ]);

        session()->put('conversation', $conversation);

        /*if ($mediaUrl) {
            // Si une image est envoyée, la traiter avec Gemini
            //$imagePath = $this->downloadImage($mediaUrl);
            $responseFromAI = $this->sendToAIServer($body, $mediaUrl);
        } else {
            // Si aucun fichier n'est envoyé, traiter le texte uniquement
            $responseFromAI = $this->sendToAIServer($body);
        }*/

        $this->sendMessage($from, $aiResponseText);

        return response()->json(['status' => 'Message sent to WhatsApp']);
    }

    /*private function downloadImage($url)
    {
        $imageContents = file_get_contents($url);
        $imageName = uniqid() . '.jpg'; // Générer un nom de fichier unique
        $imagePath = storage_path('app/images/') . $imageName;

        // Sauvegarder l'image dans le répertoire de stockage
        Storage::put('images/' . $imageName, $imageContents);

        return $imagePath;
    }

    private function sendToAIServer($message, $imagePath = null)
    {
        $response = $this->geminiService->generateContent($message, $imagePath);
        $result = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return $result;
    }*/

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