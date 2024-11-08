<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class ExcelExpertController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Gère la demande pour un assistant expert Excel.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function askExcelExpert(Request $request)
    {
        $userMessage = $request->input('message');

        // Vérifier si le message utilisateur est vide
        if (empty($userMessage)) {
            return response()->json(['error' => 'Le message ne peut pas être vide.'], 400);
        }

        // Dialogue pour envoyer à l'API, avec un contexte Excel
        $dialogue = [
            [
                'parts' => [
                    [
                        'text' => "Tu es un assistant expert en Excel. Réponds aux questions sur Excel de manière détaillée et précise, en expliquant les étapes clairement."
                    ]
                ]
            ],
            [
                'parts' => [
                    [
                        'text' => $userMessage
                    ]
                ]
            ]
        ];

        // Utiliser la méthode chatWithModel pour obtenir une réponse de l'API
        $response = $this->geminiService->chatWithModel($dialogue);

        /*if (isset($response['error'])) {
            return response()->json(['error' => $response['error']], 500);
        }*/

        // Retourner la réponse de l'API
        return response()->json([
            'response' => $response['candidates'][0]['content']['parts'][0]['text'] ?? "Aucune réponse disponible."
        ]);
    }
}
