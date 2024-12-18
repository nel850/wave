<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


    // Route::get('/incoming-sms', [SmsWebhookController::class, 'handle']);
    // Route::post('/send-sms', [SmsWebhookController::class, 'send']);


