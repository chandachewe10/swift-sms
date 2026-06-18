<?php

namespace App\Services;

use App\Models\User;
use App\Models\WhatsAppConfig;
use App\Models\WhatsAppEmbeddedSignupLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaEmbeddedSignupService
{
    public function handle(User $user, array $payload): array
    {
        $this->log($user->id, 'incoming_request', $payload, null, 'info', 'Embedded signup callback received');

        $code = $payload['code'] ?? null;
        if (! $code) {
            $this->log($user->id, 'validation_error', $payload, null, 'error', 'Authorization code is missing');

            return [
                'success' => false,
                'message' => 'Authorization code is required.',
            ];
        }

        $tokenResponse = $this->exchangeCode($code);
        $this->log($user->id, 'token_exchange', ['code' => $code], $tokenResponse, $tokenResponse['success'] ? 'success' : 'error');

        if (! $tokenResponse['success']) {
            return [
                'success' => false,
                'message' => $tokenResponse['message'] ?? 'Failed to exchange authorization code.',
                'data' => $tokenResponse['data'] ?? null,
            ];
        }

        $accessToken = $tokenResponse['data']['access_token'];
        $phoneNumberId = $payload['phone_number_id'] ?? null;
        $wabaId = $payload['waba_id'] ?? $payload['business_account_id'] ?? null;
        $businessId = $payload['business_id'] ?? null;
        $phoneNumber = $payload['phone_number'] ?? null;

        if ($phoneNumberId) {
            $phoneDetails = $this->fetchPhoneNumberDetails($phoneNumberId, $accessToken);
            $this->log($user->id, 'phone_fetch', ['phone_number_id' => $phoneNumberId], $phoneDetails, $phoneDetails['success'] ? 'success' : 'error');

            if ($phoneDetails['success']) {
                $phoneNumber = $phoneNumber ?? ($phoneDetails['data']['display_phone_number'] ?? null);
            }
        } elseif ($wabaId) {
            $phones = $this->fetchWabaPhoneNumbers($wabaId, $accessToken);
            $this->log($user->id, 'waba_phone_fetch', ['waba_id' => $wabaId], $phones, $phones['success'] ? 'success' : 'error');

            if ($phones['success'] && ! empty($phones['data'][0])) {
                $phoneNumberId = $phones['data'][0]['id'] ?? null;
                $phoneNumber = $phoneNumber ?? ($phones['data'][0]['display_phone_number'] ?? null);
            }
        }

        if (! $phoneNumberId) {
            $this->log($user->id, 'validation_error', $payload, null, 'error', 'WhatsApp phone number ID was not returned by Meta');

            return [
                'success' => false,
                'message' => 'WhatsApp phone number ID was not returned. Please complete registration again.',
            ];
        }

        $config = WhatsAppConfig::updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone_number_id' => $phoneNumberId,
                'phone_number' => $phoneNumber,
                'business_account_id' => $wabaId,
                'business_id' => $businessId,
                'access_token' => $accessToken,
                'app_id' => config('services.meta_whatsapp.app_id'),
            ]
        );

        $result = [
            'success' => true,
            'message' => 'WhatsApp phone number registered successfully.',
            'data' => [
                'phone_number_id' => $config->phone_number_id,
                'phone_number' => $config->phone_number,
                'business_account_id' => $config->business_account_id,
                'business_id' => $config->business_id,
            ],
        ];

        $this->log($user->id, 'config_saved', $payload, $result, 'success', 'Company WhatsApp config saved');

        Log::info('Meta embedded signup completed', [
            'user_id' => $user->id,
            'phone_number_id' => $config->phone_number_id,
            'business_account_id' => $config->business_account_id,
        ]);

        return $result;
    }

    public function exchangeCode(string $code): array
    {
        $graphVersion = config('services.meta_whatsapp.graph_version', 'v25.0');
        $url = "https://graph.facebook.com/{$graphVersion}/oauth/access_token";

        $response = Http::asForm()->post($url, [
            'client_id' => config('services.meta_whatsapp.app_id'),
            'client_secret' => config('services.meta_whatsapp.app_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => config('services.meta_whatsapp.redirect_uri'),
        ]);

        $body = $response->json() ?? ['raw' => $response->body()];

        if ($response->failed() || empty($body['access_token'])) {
            return [
                'success' => false,
                'message' => $body['error']['message'] ?? 'Token exchange failed.',
                'data' => $body,
            ];
        }

        return [
            'success' => true,
            'data' => $body,
        ];
    }

    private function fetchPhoneNumberDetails(string $phoneNumberId, string $accessToken): array
    {
        $graphVersion = config('services.meta_whatsapp.graph_version', 'v25.0');
        $response = Http::withToken($accessToken)
            ->get("https://graph.facebook.com/{$graphVersion}/{$phoneNumberId}", [
                'fields' => 'display_phone_number,verified_name',
            ]);

        $body = $response->json() ?? ['raw' => $response->body()];

        if ($response->failed()) {
            return [
                'success' => false,
                'data' => $body,
            ];
        }

        return [
            'success' => true,
            'data' => $body,
        ];
    }

    private function fetchWabaPhoneNumbers(string $wabaId, string $accessToken): array
    {
        $graphVersion = config('services.meta_whatsapp.graph_version', 'v25.0');
        $response = Http::withToken($accessToken)
            ->get("https://graph.facebook.com/{$graphVersion}/{$wabaId}/phone_numbers", [
                'fields' => 'id,display_phone_number,verified_name',
            ]);

        $body = $response->json() ?? ['raw' => $response->body()];

        if ($response->failed()) {
            return [
                'success' => false,
                'data' => $body,
            ];
        }

        return [
            'success' => true,
            'data' => $body['data'] ?? [],
        ];
    }

    private function log(
        ?int $userId,
        string $step,
        ?array $requestPayload,
        ?array $responsePayload,
        string $status = 'info',
        ?string $message = null
    ): void {
        WhatsAppEmbeddedSignupLog::create([
            'user_id' => $userId,
            'step' => $step,
            'request_payload' => $requestPayload,
            'response_payload' => $responsePayload,
            'status' => $status,
            'message' => $message,
        ]);
    }
}
