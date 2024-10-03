<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:";
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    // 1. Générer du contenu à partir de texte seul ou de texte avec image
    public function generateContent($text, $imagePath = null)
    {
        $contents = [
            [
                'parts' => [
                    ['text' => $text]
                ]
            ]
        ];

        // Si une image est fournie, l'ajouter au payload
        if ($imagePath) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $contents[0]['parts'][] = [
                "inline_data" => [
                    "mime_type" => "image/jpeg",
                    "data" => $imageData
                ]
            ];
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl . 'generateContent?key=' . $this->apiKey, [
            'contents' => $contents
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    // 2. Chat interactif (multi-turn chat)
    public function chatWithModel(array $dialogue)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl . 'generateContent?key=' . $this->apiKey, [
            'contents' => $dialogue,
            'generationConfig' => [
                'maxOutputTokens' => 800,
                'stopSequences' => [],
                'temperature' => 1.5,
            ],

        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    // 3. Utilisation du streaming pour la génération de texte partielle
    public function streamGenerateContent($text)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache',
        ])->post($this->apiUrl . 'streamGenerateContent?alt=sse&key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ]
        ]);

        return $response->body(); // Retourne la réponse partielle sous forme de flux
    }

    // 4. Générer du texte avec configuration personnalisée
    public function generateConfiguredText($text, array $config)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl . 'generateContent?key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ],
            'generationConfig' => $config
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
