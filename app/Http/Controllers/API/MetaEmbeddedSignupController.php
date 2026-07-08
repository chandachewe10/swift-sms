<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Filament\Pages\RegisterPhoneNumberPage;
use App\Models\WhatsAppPendingOnboarding;
use App\Services\MetaEmbeddedSignupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetaEmbeddedSignupController extends Controller
{
    /**
     * Handle the authorization-code callback from Meta Embedded Signup.
     */
    public function store(Request $request, MetaEmbeddedSignupService $service): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'phone_number_id' => ['nullable', 'string'],
            'waba_id' => ['nullable', 'string'],
            'business_account_id' => ['nullable', 'string'],
            'business_id' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string'],
            'raw_payload' => ['nullable', 'array'],
        ]);

        $payload = array_merge($validated, [
            'raw_payload' => $validated['raw_payload'] ?? $request->all(),
        ]);

        $result = $service->handle($request->user(), $payload);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Record a pending onboarding session just before the user is redirected to Meta.
     * This allows the PARTNER_APP_INSTALLED webhook to resolve the correct user later.
     */
    public function startOnboarding(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $appId = config('services.meta_whatsapp.app_id');

        WhatsAppPendingOnboarding::updateOrCreate(
            ['user_id' => $userId, 'status' => 'pending'],
            ['app_id' => $appId]
        );

        return response()->json([
            'success' => true,
            'onboard_url' => RegisterPhoneNumberPage::getOnboardUrl(),
        ]);
    }
}
