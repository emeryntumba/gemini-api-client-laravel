<?php

use App\Http\Controllers\API\ExcelExpertController;
use App\Http\Controllers\API\WhatsappController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/whatsapp/webhook', [WhatsappController::class, 'receiveMessage']);


Route::post('/excel/agent', [ExcelExpertController::class], 'askExcelExpert');
