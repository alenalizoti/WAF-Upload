<?php

use App\Http\Controllers\SecureUploadController;
use App\Http\Controllers\VulnerableUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('upload');
});

// Ranjiva ruta - bez ikakve zastite (za demonstraciju napada).
Route::post('/vulnerable/upload', [VulnerableUploadController::class, 'store']);

// Zasticena ruta - WAF proverava fajl pre cuvanja.
Route::post('/secure/upload', [SecureUploadController::class, 'store']);
