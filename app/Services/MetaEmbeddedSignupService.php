<?php

namespace App\Services;

use App\Models\User;
use App\Models\WhatsAppConfig;
use App\Models\WhatsAppEmbeddedSignupLog;
use App\Models\WhatsAppPendingOnboarding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaEmbeddedSignupService
{
    public function handle(User $user, array $payload): array
    {
        // If a state token was relayed from the OAuth redirect, resolve the
        // definitive user — important when multiple users are in pending state.
        if (! empty($payload['state'])) {
            $pending = WhatsAppPendingOnboarding::where('state_token', $payload['state'])->first();
            if ($pending && $pending->user_id && $pending->user) {
                $user = $pending->user;
            }
        }

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
                'waba_id' => $wabaId,
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
                'waba_id' => $config->waba_id,
                'business_id' => $config->business_id,
            ],
        ];

        $this->log($user->id, 'config_saved', $payload, $result, 'success', 'Company WhatsApp config saved');

        // Link the pending onboarding session to this Meta business ID so the
        // PARTNER_APP_INSTALLED webhook can resolve the user even when the webhook
        // and browser callback arrive in different orders.
        if ($businessId) {
            $this->linkPendingOnboarding($user->id, $businessId, $wabaId);
        }

        // Subscribe the WABA to receive webhook notifications
        if ($wabaId) {
            $subscribeResult = $this->subscribeWaba($wabaId, $accessToken);
            $this->log($user->id, 'waba_subscribe', ['waba_id' => $wabaId], $subscribeResult, $subscribeResult['success'] ? 'success' : 'error');
        }

        Log::info('Meta embedded signup completed', [
            'user_id' => $user->id,
            'phone_number_id' => $config->phone_number_id,
            'business_account_id' => $config->business_account_id,
            'waba_id' => $config->waba_id,
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

    /**
     * Subscribe a WABA to receive webhook notifications from this app.
     */
    public function subscribeWaba(string $wabaId, string $accessToken): array
    {
        $graphVersion = config('services.meta_whatsapp.graph_version', 'v25.0');
        $response = Http::withToken($accessToken)
            ->post("https://graph.facebook.com/{$graphVersion}/{$wabaId}/subscribed_apps");

        $body = $response->json() ?? ['raw' => $response->body()];

        if ($response->failed()) {
            Log::warning('WABA subscription failed', ['waba_id' => $wabaId, 'response' => $body]);

            return [
                'success' => false,
                'message' => $body['error']['message'] ?? 'WABA subscription failed.',
                'data' => $body,
            ];
        }

        Log::info('WABA subscribed to webhook', ['waba_id' => $wabaId]);

        return [
            'success' => true,
            'data' => $body,
        ];
    }

    /**
     * Fetch all phone numbers registered under a WABA.
     */
    public function fetchWabaPhoneNumbers(string $wabaId, string $accessToken): array
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

    /**
     * Update the pending onboarding record with the resolved Meta business ID.
     *
     * Handles three scenarios:
     * A) Webhook-first stub exists (user_id=null, meta_business_id set)  → fill in user_id
     * B) User session exists (user_id set, meta_business_id=null)         → fill in business data
     * C) Neither found                                                    → create completed record
     *
     * In scenario A, we also mark the corresponding user session (if any) as completed
     * to avoid leaving a dangling pending row behind.
     */
    private function linkPendingOnboarding(int $userId, string $metaBusinessId, ?string $wabaId): void
    {
        // Scenario A: webhook created a stub before the browser callback arrived
        $stub = WhatsAppPendingOnboarding::where('meta_business_id', $metaBusinessId)
            ->whereNull('user_id')
            ->whereIn('status', ['pending', 'webhook_received'])
            ->latest()
            ->first();

        if ($stub) {
            $stub->update([
                'user_id' => $userId,
                'waba_id' => $wabaId ?? $stub->waba_id,
                'status'  => 'completed',
            ]);

            // Clean up the dangling user session row (created on page load) if it
            // still exists separately — prevents duplicate pending rows for the user.
            WhatsAppPendingOnboarding::where('user_id', $userId)
                ->whereNull('meta_business_id')
                ->where('status', 'pending')
                ->update(['status' => 'completed']);

            return;
        }

        // Scenario B: user loaded the page (session row exists), webhook not yet processed
        $pending = WhatsAppPendingOnboarding::forUser($userId);

        if ($pending) {
            $pending->update([
                'meta_business_id' => $metaBusinessId,
                'waba_id'          => $wabaId ?? $pending->waba_id,
                'status'           => 'completed',
            ]);

            return;
        }

        // Scenario C: fallback — create a completed record for audit purposes
        WhatsAppPendingOnboarding::create([
            'user_id'          => $userId,
            'meta_business_id' => $metaBusinessId,
            'waba_id'          => $wabaId,
            'app_id'           => config('services.meta_whatsapp.app_id'),
            'status'           => 'completed',
        ]);
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
