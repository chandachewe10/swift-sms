<?php

namespace App\Services;

use App\Models\Messages;
use App\Models\SenderId;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsDispatcher
{
    /**
     * Send SMS using the platform-configured provider (Zamtel or Mocean).
     *
     * @param  string   $companyId  user_id of the sending account
     * @param  array    $numbers    Array of phone numbers (local or E.164)
     * @param  string   $message    Message text
     * @param  array    $options    flash (bool), schedule (datetime string)
     * @return array{success: bool, responseText: string, statusCode: int, raw: array}
     */
    public static function send(
        string $companyId,
        array  $numbers,
        string $message,
        array  $options = []
    ): array {
        $provider = SystemSetting::get('sms_provider', 'zamtel');

        if ($provider === 'mocean') {
            return self::sendViaMocean($companyId, $numbers, $message, $options);
        }

        return self::sendViaZamtel($companyId, $numbers, $message);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Zamtel (existing)
    // ──────────────────────────────────────────────────────────────────────────

    private static function sendViaZamtel(string $companyId, array $numbers, string $message): array
    {
        $senderId = SenderId::where('company_id', $companyId)
            ->where('is_approved', 1)
            ->first()?->name;

        if (empty($senderId)) {
            return [
                'success'      => false,
                'responseText' => 'No approved Sender ID found.',
                'statusCode'   => 422,
                'raw'          => [],
            ];
        }

        $contactsString  = implode(',', $numbers);
        $url = env('BULK_SMS_BASE_URI')
            . '/api_key/'    . urlencode(env('BULK_SMS_TOKEN'))
            . '/contacts/'   . urlencode($contactsString)
            . '/senderId/'   . urlencode($senderId)
            . '/message/'    . urlencode($message);

        try {
            $response     = Http::timeout(300)->get($url);
            $responseData = $response->json() ?? [];
            $statusCode   = $responseData['statusCode'] ?? 0;
            $responseText = $responseData['responseText'] ?? 'No response from network.';

            return [
                'success'      => $statusCode == 202,
                'responseText' => $responseText,
                'statusCode'   => $response->status(),
                'raw'          => $responseData,
            ];
        } catch (\Throwable $e) {
            Log::error('Zamtel SMS error: ' . $e->getMessage());
            return [
                'success'      => false,
                'responseText' => 'Zamtel request failed: ' . $e->getMessage(),
                'statusCode'   => 500,
                'raw'          => [],
            ];
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Mocean
    // ──────────────────────────────────────────────────────────────────────────

    private static function sendViaMocean(
        string $companyId,
        array  $numbers,
        string $message,
        array  $options
    ): array {
        $token = SystemSetting::get('mocean_api_token');

        // Always use the company's own approved Sender ID — never expose platform credentials
        $senderId = SenderId::where('company_id', $companyId)
            ->where('is_approved', 1)
            ->first()?->name;

        if (empty($token)) {
            return [
                'success'      => false,
                'responseText' => 'Mocean API token is not configured. Please set it in SMS Provider Settings.',
                'statusCode'   => 422,
                'raw'          => [],
            ];
        }

        if (empty($senderId)) {
            return [
                'success'      => false,
                'responseText' => 'No Sender ID configured for Mocean.',
                'statusCode'   => 422,
                'raw'          => [],
            ];
        }

        // Normalize Zambian local numbers to E.164
        $normalized = array_map([MoceanService::class, 'normalizeNumber'], $numbers);

        $service = new MoceanService($token);

        // Mocean allows up to 500 per request; batch if needed
        $batches      = array_chunk($normalized, 500);
        $successCount = 0;
        $lastResult   = [];

        foreach ($batches as $batch) {
            $result = $service->send($senderId, $batch, $message, $options);
            if ($result['success']) {
                $successCount += count($batch);
            }
            $lastResult = $result;
        }

        return [
            'success'      => $successCount > 0,
            'responseText' => $lastResult['responseText'] ?? 'No response.',
            'statusCode'   => $lastResult['statusCode']   ?? 500,
            'raw'          => $lastResult['raw']           ?? [],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    public static function activeProvider(): string
    {
        return SystemSetting::get('sms_provider', 'zamtel');
    }

    public static function isMocean(): bool
    {
        return static::activeProvider() === 'mocean';
    }
}
