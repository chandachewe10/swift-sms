<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MessagesAPI;
use App\Http\Controllers\API\WhatsAppMessagesAPI;
use App\Http\Controllers\API\MetaEmbeddedSignupController;
use App\Http\Controllers\API\WhatsAppWebhookController;


Route::middleware([
    'auth:sanctum',
])->group(function () {

    Route::get('send_message', [MessagesAPI::class, 'store']);

    Route::post('send_whatsapp_message', [WhatsAppMessagesAPI::class, 'store']);

    Route::post('meta_embedded_signup', [MetaEmbeddedSignupController::class, 'store'])
        ->name('meta.embedded-signup');

});


Route::get('whatsapp/webhook', [WhatsAppWebhookController::class, 'verify']);

Route::post('whatsapp/webhook', [WhatsAppWebhookController::class, 'handle']);

Route::get('lenco', [MessagesAPI::class, 'paymentResponse']);