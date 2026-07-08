<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request)
    {
        $verifyToken = config('services.meta_whatsapp.verify_token');

        if ($request->hub_verify_token === $verifyToken) {
            return response($request->hub_challenge, 200);
        }

        return response('Invalid verify token', 403);
    }


    public function handle(Request $request)
    {
        // Log everything Meta sends
        Log::info('WhatsApp Webhook Received:', [
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
            'raw_body' => $request->getContent(),
        ]);

        return response()->json([
            'success' => true,
        ], 200);
    }
}