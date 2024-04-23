<?php

use Illuminate\Support\Facades\Route;
use Lockminds\NBCPaymentGateway\Controllers\NBCGatewayController;

Route::post('/status', [NBCGatewayController::class,'status'])->name('status');
Route::post('/verify', [NBCGatewayController::class,'verifyRequest'])->name('verifyRequest');
Route::post('/post-payment', [NBCGatewayController::class,'postPaymentRequest'])->name('postPaymentRequest');
Route::get('/get-reference-number', [NBCGatewayController::class,'create'])->name('create');
