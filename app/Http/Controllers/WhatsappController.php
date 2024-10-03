<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        $mediaUrl = $request->input('mediaUrl'); // URL de l'image
        $mediaType = $request->input('MediaContentType0'); // Type MIME

        $responseFromAI = '';

        if ($mediaUrl) {
            // Si une image est envoyée, la traiter avec Gemini
            //$imagePath = $this->downloadImage($mediaUrl);
            $responseFromAI = $this->sendToAIServer($body, $mediaUrl);
        } else {
            // Si aucun fichier n'est envoyé, traiter le texte uniquement
            $responseFromAI = $this->sendToAIServer($body);
        }

        $this->sendMessage($from, $responseFromAI);

        return response()->json(['status' => 'Message sent to WhatsApp']);
    }

    private function downloadImage($url)
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
