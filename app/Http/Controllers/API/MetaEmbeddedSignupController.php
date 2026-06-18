<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\MetaEmbeddedSignupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetaEmbeddedSignupController extends Controller
{
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
}
