<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MessagesAPI;

Route::middleware([
    'auth:sanctum',
])->group(function () {
    Route::get('send_message', [MessagesAPI::class, 'store']);

});