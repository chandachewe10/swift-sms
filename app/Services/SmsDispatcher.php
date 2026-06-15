<?php

namespace App\Services;

use App\Models\SenderId;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsDispatcher
{
    /**
     * Detect whether a (normalized) number is a local Zambian number.
     * Zambian numbers after normalization: 12 digits starting with 260.
     */
    public static function isZambianNumber(string $number): bool
    {
        $digits = preg_replace('/\D/', '', $number);
        return strlen($digits) === 12 && str_starts_with($digits, '260');
    }

    /**
     * Split an array of numbers into ['local' => [...], 'international' => [...]].
     */
    public static function splitByType(array $numbers): array
    {
        $local         = [];
        $international = [];

        foreach ($numbers as $number) {
            $normalized = MoceanService::normalizeNumber($number);
            if (self::isZambianNumber($normalized)) {
                $local[] = $number;
            } else {
                $international[] = $normalized; // pass normalized form for international
            }
        }

        return ['local' => $local, 'international' => $international];
    }

    /**
     * Send SMS — automatically routes local numbers via Zamtel and
     * international numbers via Mocean, regardless of system setting.
     *
     * Returns:
     *   success            bool   — true if at least one group succeeded
     *   responseText       string — combined status message
     *   statusCode         int
     *   localCount         int    — number of local numbers successfully sent
     *   internationalCount int    — number of international numbers successfully sent
     *   raw                array
     */
    public static function send(
        string $companyId,
        array  $numbers,
        string $message,
        array  $options = []
    ): array {
        $split = self::splitByType($numbers);

        $localResult = ['success' => true, 'responseText' => '', 'statusCode' => 200, 'raw' => []];
        $intlResult  = ['success' => true, 'responseText' => '', 'statusCode' => 200, 'raw' => []];

        $localCount  = 0;
        $intlCount   = 0;

        $devMode = self::isDevMode();

        // ── Local → Zamtel (or mock in dev mode) ──────────────────────────
        if (! empty($split['local'])) {
            if ($devMode) {
                $localResult = [
                    'success'      => true,
                    'responseText' => '[DEV MODE] ' . count($split['local']) . ' local SMS(es) simulated — not sent.',
                    'statusCode'   => 202,
                    'raw'          => ['test' => true],
                ];
                Log::info('SmsDispatcher [DEV MODE]: mocked Zamtel send', ['numbers' => $split['local']]);
            } else {
                $localResult = self::sendViaZamtel($companyId, $split['local'], $message);
            }
            if ($localResult['success']) {
                $localCount = count($split['local']);
            }
        }

        // ── International → Mocean (test flag in dev mode) ────────────────
        if (! empty($split['international'])) {
            if ($devMode) {
                $options['test_mode'] = true;
            }
            $intlResult = self::sendViaMocean($companyId, $split['international'], $message, $options);
            if ($intlResult['success']) {
                $intlCount = count($split['international']);
            }
        }

        $parts = [];
        if (! empty($split['local'])) {
            $parts[] = "Local: " . ($localResult['success'] ? "{$localCount} sent" : "failed — " . $localResult['responseText']);
        }
        if (! empty($split['international'])) {
            $parts[] = "International: " . ($intlResult['success'] ? "{$intlCount} sent" : "failed — " . $intlResult['responseText']);
        }

        return [
            'success'            => $localCount > 0 || $intlCount > 0,
            'responseText'       => implode(' | ', $parts) ?: 'No numbers to send.',
            'statusCode'         => $localResult['statusCode'] ?: $intlResult['statusCode'],
            'localCount'         => $localCount,
            'internationalCount' => $intlCount,
            'raw'                => ['local' => $localResult['raw'], 'international' => $intlResult['raw']],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Zamtel (local)
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

        $contactsString = implode(',', $numbers);
        $url = env('BULK_SMS_BASE_URI')
            . '/api_key/'  . urlencode(env('BULK_SMS_TOKEN'))
            . '/contacts/' . urlencode($contactsString)
            . '/senderId/' . urlencode($senderId)
            . '/message/'  . urlencode($message);

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
    // Mocean (international)
    // ──────────────────────────────────────────────────────────────────────────

    private static function sendViaMocean(
        string $companyId,
        array  $numbers,
        string $message,
        array  $options
    ): array {
        $token = SystemSetting::get('mocean_api_token');

        $senderId = SenderId::where('company_id', $companyId)
            ->where('is_approved', 1)
            ->first()?->name;

        if (empty($token)) {
            return [
                'success'      => false,
                'responseText' => 'International SMS is not configured. Please contact support.',
                'statusCode'   => 422,
                'raw'          => [],
            ];
        }

        if (empty($senderId)) {
            return [
                'success'      => false,
                'responseText' => 'No Sender ID configured for international SMS.',
                'statusCode'   => 422,
                'raw'          => [],
            ];
        }

        $service      = new MoceanService($token);
        $batches      = array_chunk($numbers, 500);
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

    /** Still used by the SMS Provider Settings page UI */
    public static function activeProvider(): string
    {
        return SystemSetting::get('sms_provider', 'zamtel');
    }

    public static function isMocean(): bool
    {
        return static::activeProvider() === 'mocean';
    }

    public static function isDevMode(): bool
    {
        return SystemSetting::get('development_mode', 'false') === 'true';
    }
}
