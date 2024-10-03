<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiService;
use Parsedown;
use Illuminate\Support\Facades\Storage;

class GeminiController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function generate(Request $request)
    {
        $inputText = $request->input('text');
        $image = $request->file('image');

        $response = null;

        if ($image) {
            // Convertir l'image en base64
            $imagePath = $image->getPathname();
            $imageData = base64_encode(file_get_contents($imagePath));
            //$mimeType = $image->getMimeType();

            // Appeler la méthode de GeminiService qui traite texte et image
            $response = $this->geminiService->generateContent($inputText, $imageData);
        } else {
            // Si aucune image, traiter uniquement le texte
            $response = $this->geminiService->generateContent($inputText);
        }

        $markdownText = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Utilisation de Parsedown pour convertir Markdown en HTML
        $parsedown = new Parsedown();
        $htmlContent = $parsedown->text($markdownText);

        return view('gemini-result', ['htmlContent' => $htmlContent]);
    }
}
