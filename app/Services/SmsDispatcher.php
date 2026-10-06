<?php

namespace App\Services;

use App\Models\SenderId;
use App\Models\SmsDeliveryQueue;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsDispatcher
{
    /** Max contacts per Zamtel GET request (contacts are in the URL path). */
    public const ZAMTEL_CONTACT_BATCH_SIZE = 50;

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
     *   localFailedCount   int    — local numbers queued after Zamtel rejection
     *   internationalCount int    — number of international numbers successfully sent
     *   clientFacingMessage string|null — safe message for end users when set
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

        $localCount       = 0;
        $localFailedCount = 0;
        $intlCount        = 0;

        $devMode = self::isDevMode();

        if ($devMode) {
            // ── Dev mode: send ALL numbers through Mocean ─────────────────
            // Local numbers are normalized to E.164 and passed to Mocean so
            // the test API token can deliver real messages for testing.
            $allNormalized = array_merge(
                array_map([MoceanService::class, 'normalizeNumber'], $split['local']),
                $split['international']
            );

            Log::info('SmsDispatcher [DEV MODE]: routing all numbers via Mocean', ['numbers' => $allNormalized]);

            if (! empty($allNormalized)) {
                $devResult = self::sendViaMocean($companyId, $allNormalized, $message, $options);
                if ($devResult['success']) {
                    $localCount = count($split['local']);
                    $intlCount  = count($split['international']);
                }
                // Overwrite both results so the response text is accurate
                $localResult = $devResult;
                $intlResult  = $devResult;
            }
        } else {
            // ── Production: route by number type ─────────────────────────
            if (! empty($split['local'])) {
                $localResult = self::sendViaZamtel($companyId, $split['local'], $message);
                $localCount       = $localResult['sentCount'] ?? 0;
                $localFailedCount = $localResult['failedCount'] ?? 0;
            }

            if (! empty($split['international'])) {
                $intlResult = self::sendViaMocean($companyId, $split['international'], $message, $options);
                if ($intlResult['success']) {
                    $intlCount = count($split['international']);
                }
            }
        }

        $parts = [];
        if (! empty($split['local'])) {
            if ($localFailedCount > 0) {
                $parts[] = "Local: {$localCount} sent, {$localFailedCount} queued for delivery";
            } else {
                $parts[] = "Local: " . ($localResult['success'] ? "{$localCount} sent" : "failed — " . $localResult['responseText']);
            }
        }
        if (! empty($split['international'])) {
            $parts[] = "International: " . ($intlResult['success'] ? "{$intlCount} sent" : "failed — " . $intlResult['responseText']);
        }

        $success = $localCount > 0 || $localFailedCount > 0 || $intlCount > 0;

        $clientFacingMessage = null;
        if ($localFailedCount > 0) {
            $clientFacingMessage = SmsSendSettlement::QUEUED_CLIENT_MESSAGE;
        }

        $result = [
            'success'             => $success,
            'responseText'        => implode(' | ', $parts) ?: 'No numbers to send.',
            'statusCode'          => $localResult['statusCode'] ?: $intlResult['statusCode'],
            'localCount'          => $localCount,
            'localFailedCount'    => $localFailedCount,
            'internationalCount'  => $intlCount,
            'clientFacingMessage' => $clientFacingMessage,
            'raw'                 => ['local' => $localResult['raw'], 'international' => $intlResult['raw']],
        ];

        if (! $success) {
            Log::warning('SmsDispatcher send failed', [
                'company_id'              => $companyId,
                'responseText'            => $result['responseText'],
                'statusCode'              => $result['statusCode'],
                'raw'                     => $result['raw'],
                'local_numbers'           => count($split['local']),
                'international_numbers'   => count($split['international']),
            ]);
        }

        return $result;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Zamtel (local)
    // ──────────────────────────────────────────────────────────────────────────

    private static function sendViaZamtel(string $companyId, array $numbers, string $message): array
    {
        $senderId = SenderId::normalizeName(
            SenderId::where('company_id', $companyId)
                ->where('is_approved', 1)
                ->first()
                ?->getRawOriginal('name')
        );

        if (empty($senderId)) {
            return [
                'success'      => false,
                'responseText' => 'No approved Sender ID found.',
                'statusCode'   => 422,
                'raw'          => [],
                'sentCount'    => 0,
            ];
        }

        $batches          = array_chunk($numbers, self::ZAMTEL_CONTACT_BATCH_SIZE);
        $successCount     = 0;
        $failedNumbers    = [];
        $lastResponseText = 'No response from network.';
        $lastStatusCode   = 500;
        $rawResponses     = [];

        foreach ($batches as $batchIndex => $batch) {
            $contactsString = implode(',', $batch);
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

                $batchSuccess = $statusCode == 202;

                if ($batchSuccess) {
                    $successCount += count($batch);
                } else {
                    $failedNumbers = array_merge($failedNumbers, $batch);

                    Log::warning('Zamtel SMS batch send failed', [
                        'company_id'   => $companyId,
                        'sender_id'    => $senderId,
                        'batch'        => $batchIndex + 1,
                        'batch_size'   => count($batch),
                        'http_status'  => $response->status(),
                        'response'     => $responseData,
                        'body'         => $response->body(),
                    ]);
                }

                $lastResponseText = $responseText;
                $lastStatusCode   = $response->status();
                $rawResponses[]   = $responseData;
            } catch (\Throwable $e) {
                $failedNumbers = array_merge($failedNumbers, $batch);

                Log::error('Zamtel SMS batch error', [
                    'company_id' => $companyId,
                    'sender_id'  => $senderId,
                    'batch'      => $batchIndex + 1,
                    'message'    => $e->getMessage(),
                ]);
                $lastResponseText = 'Zamtel request failed: ' . $e->getMessage();
                $lastStatusCode   = 500;
            }
        }

        $failedCount = count($failedNumbers);
        if ($failedCount > 0) {
            SmsDeliveryQueue::create([
                'company_id'         => $companyId,
                'sender_id'          => $senderId,
                'message'            => $message,
                'contacts'           => array_values($failedNumbers),
                'failed_count'       => $failedCount,
                'provider_response'  => $lastResponseText,
                'status'             => 'pending',
            ]);

            Log::warning('Zamtel SMS queued for retry', [
                'company_id'         => $companyId,
                'sender_id'          => $senderId,
                'failed_count'       => $failedCount,
                'provider_response'  => $lastResponseText,
            ]);
        }

        $total = count($numbers);
        $responseSummary = $successCount === $total
            ? ($lastResponseText ?: "{$successCount} sent")
            : "{$successCount} of {$total} sent. Last: {$lastResponseText}";

        return [
            'success'      => $successCount > 0 || $failedCount > 0,
            'responseText' => $responseSummary,
            'statusCode'   => $lastStatusCode,
            'raw'          => $rawResponses,
            'sentCount'    => $successCount,
            'failedCount'  => $failedCount,
        ];
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

        $senderId = SenderId::normalizeName(
            SenderId::where('company_id', $companyId)
                ->where('is_approved', 1)
                ->first()
                ?->getRawOriginal('name')
        );

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
