<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppConfig;
use App\Models\WhatsAppEmbeddedSignupLog;
use App\Models\WhatsAppPendingOnboarding;
use App\Services\MetaEmbeddedSignupService;
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

    public function handle(Request $request, MetaEmbeddedSignupService $signupService)
    {
        $payload = $request->all();

        Log::info('WhatsApp Webhook Received', [
            'headers' => $request->headers->all(),
            'payload' => $payload,
            'raw_body' => $request->getContent(),
        ]);

        // Process each entry in the webhook payload
        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];

            foreach ($changes as $change) {
                $field = $change['field'] ?? null;
                $value = $change['value'] ?? [];

                if ($field === 'account_update') {
                    $this->handleAccountUpdate($value, $signupService);
                }
            }
        }

        return response()->json(['success' => true], 200);
    }

    private function handleAccountUpdate(array $value, MetaEmbeddedSignupService $signupService): void
    {
        $event = $value['event'] ?? null;
        $wabaInfo = $value['waba_info'] ?? [];

        $wabaId = $wabaInfo['waba_id'] ?? null;
        $ownerBusinessId = $wabaInfo['owner_business_id'] ?? null;
        $partnerAppId = $wabaInfo['partner_app_id'] ?? null;

        Log::info('WhatsApp account_update event', [
            'event' => $event,
            'waba_id' => $wabaId,
            'owner_business_id' => $ownerBusinessId,
            'partner_app_id' => $partnerAppId,
        ]);

        if ($event !== 'PARTNER_APP_INSTALLED') {
            // Other account_update events (e.g. MM_LITE_TERMS_SIGNED) are logged above
            // but require no automated action at this stage.
            return;
        }

        if (! $wabaId || ! $ownerBusinessId) {
            Log::warning('PARTNER_APP_INSTALLED missing waba_id or owner_business_id', $value);

            return;
        }

        $this->processPartnerAppInstalled($wabaId, $ownerBusinessId, $partnerAppId, $signupService);
    }

    private function processPartnerAppInstalled(
        string $wabaId,
        string $ownerBusinessId,
        ?string $partnerAppId,
        MetaEmbeddedSignupService $signupService
    ): void {
        $appId = $partnerAppId ?? config('services.meta_whatsapp.app_id');

        // --- Step 1: Resolve the local user via the pending onboarding session ---

        // Try to find an existing WhatsAppConfig that was already saved by the browser
        // callback arriving ahead of this webhook.
        $existingConfig = WhatsAppConfig::where('business_id', $ownerBusinessId)->first();

        if ($existingConfig) {
            // Config already exists from the frontend callback; patch any missing fields
            // and ensure WABA subscription is active.
            $updated = [];

            if (! $existingConfig->waba_id) {
                $updated['waba_id'] = $wabaId;
            }

            if (! $existingConfig->app_id) {
                $updated['app_id'] = $appId;
            }

            if ($updated) {
                $existingConfig->update($updated);
            }

            $this->logOnboarding(
                $existingConfig->user_id,
                'webhook_partner_app_installed',
                compact('wabaId', 'ownerBusinessId', 'partnerAppId'),
                ['action' => 'config_already_exists', 'config_id' => $existingConfig->id],
                'success',
                'PARTNER_APP_INSTALLED: WhatsAppConfig already present, patched waba fields'
            );

            $this->ensureWabaSubscribed($existingConfig, $wabaId, $signupService);

            return;
        }

        // Look for a pending onboarding that has already been linked to this Meta business.
        $pendingByBusiness = WhatsAppPendingOnboarding::forMetaBusiness($ownerBusinessId);

        if ($pendingByBusiness && $pendingByBusiness->user_id) {
            $this->completeOnboardingForUser(
                $pendingByBusiness->user_id,
                $wabaId,
                $ownerBusinessId,
                $appId,
                $signupService
            );
            $pendingByBusiness->update(['waba_id' => $wabaId, 'status' => 'completed']);

            return;
        }

        // --- Step 2: Webhook arrived before the browser callback updated the pending record ---
        // Store the webhook data as a stub so that when the frontend callback eventually fires,
        // linkPendingOnboarding() in MetaEmbeddedSignupService can match and complete it.

        Log::warning('PARTNER_APP_INSTALLED: no pending onboarding found for owner_business_id, creating stub', [
            'owner_business_id' => $ownerBusinessId,
            'waba_id' => $wabaId,
        ]);

        // Upsert: update any existing stub for this business, or create a new one
        $stub = WhatsAppPendingOnboarding::where('meta_business_id', $ownerBusinessId)
            ->whereNull('user_id')
            ->first();

        if ($stub) {
            $stub->update([
                'waba_id' => $wabaId,
                'app_id' => $appId,
                'status' => 'webhook_received',
            ]);
        } else {
            WhatsAppPendingOnboarding::create([
                'user_id' => null,
                'meta_business_id' => $ownerBusinessId,
                'waba_id' => $wabaId,
                'app_id' => $appId,
                'status' => 'webhook_received',
            ]);
        }

        $this->logOnboarding(
            null,
            'webhook_partner_app_installed_stub',
            compact('wabaId', 'ownerBusinessId', 'partnerAppId'),
            ['stub_created' => true],
            'info',
            'PARTNER_APP_INSTALLED stub created; awaiting browser callback to resolve user'
        );
    }

    /**
     * Create or update the WhatsAppConfig for the identified user, then fetch phone
     * numbers and subscribe the WABA.  Called when the webhook arrives with full context.
     */
    private function completeOnboardingForUser(
        int $userId,
        string $wabaId,
        string $ownerBusinessId,
        string $appId,
        MetaEmbeddedSignupService $signupService
    ): void {
        $accessToken = $this->resolveAccessToken($userId);

        // Persist what we know; phone_number_id / phone_number will be filled once
        // we can call the Graph API.
        $config = WhatsAppConfig::updateOrCreate(
            ['user_id' => $userId],
            [
                'business_account_id' => $wabaId,
                'waba_id' => $wabaId,
                'business_id' => $ownerBusinessId,
                'app_id' => $appId,
                // Preserve any access_token that was already stored
                ...($accessToken ? ['access_token' => $accessToken] : []),
            ]
        );

        $this->logOnboarding(
            $userId,
            'webhook_config_upserted',
            compact('wabaId', 'ownerBusinessId', 'appId'),
            ['config_id' => $config->id],
            'success',
            'WhatsAppConfig upserted from PARTNER_APP_INSTALLED webhook'
        );

        if (! $accessToken) {
            Log::warning('PARTNER_APP_INSTALLED: no access token available; skipping phone fetch and WABA subscription', [
                'user_id' => $userId,
                'waba_id' => $wabaId,
            ]);

            return;
        }

        // --- Step 3: Fetch phone numbers for the WABA ---
        $phones = $signupService->fetchWabaPhoneNumbers($wabaId, $accessToken);

        $this->logOnboarding(
            $userId,
            'webhook_phone_fetch',
            ['waba_id' => $wabaId],
            $phones,
            $phones['success'] ? 'success' : 'error'
        );

        if ($phones['success'] && ! empty($phones['data'][0])) {
            $firstPhone = $phones['data'][0];
            $config->update([
                'phone_number_id' => $firstPhone['id'] ?? $config->phone_number_id,
                'phone_number' => $firstPhone['display_phone_number'] ?? $config->phone_number,
            ]);
        }

        // --- Step 4: Subscribe WABA to receive webhooks ---
        $subscribeResult = $signupService->subscribeWaba($wabaId, $accessToken);

        $this->logOnboarding(
            $userId,
            'webhook_waba_subscribe',
            ['waba_id' => $wabaId],
            $subscribeResult,
            $subscribeResult['success'] ? 'success' : 'error'
        );
    }

    /**
     * Ensure the WABA is subscribed.  Used when the WhatsAppConfig already exists
     * (browser callback arrived before the webhook).
     */
    private function ensureWabaSubscribed(
        WhatsAppConfig $config,
        string $wabaId,
        MetaEmbeddedSignupService $signupService
    ): void {
        $accessToken = $config->access_token ?? $this->resolveAccessToken($config->user_id);

        if (! $accessToken) {
            Log::warning('ensureWabaSubscribed: no access token available', [
                'user_id' => $config->user_id,
                'waba_id' => $wabaId,
            ]);

            return;
        }

        $result = $signupService->subscribeWaba($wabaId, $accessToken);

        $this->logOnboarding(
            $config->user_id,
            'webhook_waba_subscribe_ensure',
            ['waba_id' => $wabaId],
            $result,
            $result['success'] ? 'success' : 'error'
        );
    }

    /**
     * Resolve the best available access token for a given user.
     * Prefers the stored user token; falls back to the system user token if configured.
     */
    private function resolveAccessToken(int $userId): ?string
    {
        $config = WhatsAppConfig::where('user_id', $userId)->first();

        if ($config && $config->access_token) {
            return $config->access_token;
        }

        return config('services.meta_whatsapp.system_user_token') ?: null;
    }

    private function logOnboarding(
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
