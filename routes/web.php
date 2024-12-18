<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Chat\Index;
use App\Livewire\Chat\Chat;
use App\Livewire\Recipients;
use Illuminate\Http\Request;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Http\Controllers\SmsWebhookController;
use App\Livewire\Chat\ChatBox;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/webhooks', [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhooks', [WhatsAppWebhookController::class, 'handle']);


#sms integration

Route::any('/sms/receive', [SmsWebhookController::class, 'handle'])
->name('sms.receive');



Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function (){
    Route::get('/chat', Index::class)->name('chat.index');
    Route::get('/chat/{query}', Chat::class)->name('chat');
    Route::get('/recipients', Recipients::class)->name('recipients');
});

