<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected $apiUrl="https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent";
    protected $apiKey;

    public function __construct(){
        $this->apiKey = config('services.gemini.key');
    }

    public function generateContent($text)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $text]
                        ]
                    ]
                ]
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
