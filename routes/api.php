<?php

use App\Http\Controllers\API\WhatsappController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('api')->group(function(){
    Route::post('/whatsapp/webhook', [WhatsappController::class, 'receiveMessage']);
});
