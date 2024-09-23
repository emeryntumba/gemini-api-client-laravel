<?php

use App\Http\Controllers\GeminiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/gemini', function () {
    return view('gemini-form');
})->name('gemini.form');

Route::post('/gemini/generate', [GeminiController::class, 'generate'])->name('gemini.generate');
