<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MessagesAPI;
use App\Http\Controllers\API\WhatsAppMessagesAPI;

Route::middleware([
    'auth:sanctum',
])->group(function () {
    Route::get('send_message', [MessagesAPI::class, 'store']);
    Route::post('send_whatsapp_message', [WhatsAppMessagesAPI::class, 'store']);

});


Route::get('lenco', [MessagesAPI::class, 'paymentResponse']);