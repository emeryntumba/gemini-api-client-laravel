<?php

use App\Http\Controllers\GeminiController;
use App\Http\Controllers\WhatsappController;
use App\Http\Middleware\SkipNgrokWarning;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([SkipNgrokWarning::class])->group(function () {
    Route::get('/gemini', function () {
        return view('gemini-form');
    })->name('gemini.form');

    Route::post('/gemini/generate', [GeminiController::class, 'generate'])->name('gemini.generate');
});

