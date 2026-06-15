<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoceanService
{
    protected string $baseUrl = 'https://rest.moceanapi.com/rest/2';

    public function __construct(protected string $apiToken) {}

    /**
     * Send SMS via Mocean API.
     *
     * @param  string          $from     Sender ID / name
     * @param  string|string[] $to       One or many E.164 phone numbers
     * @param  string          $message  Text content
     * @param  array           $options  flash (bool), schedule (Y-m-d H:i:s GMT+8)
     */
    public function send(string $from, string|array $to, string $message, array $options = []): array
    {
        if (is_array($to)) {
            $to = implode(',', $to);
        }

        $payload = [
            'mocean-from'        => $from,
            'mocean-to'          => $to,
            'mocean-text'        => $message,
            'mocean-resp-format' => 'json',
        ];

        if (! empty($options['flash'])) {
            $payload['mocean-mclass']  = 1;
            $payload['mocean-alt-dcs'] = 1;
        }

        // In development mode, tell Mocean to validate but never actually send
        if (! empty($options['test_mode'])) {
            $payload['mocean-test'] = 'Y';
        }

        if (! empty($options['schedule'])) {
            // Mocean expects YYYY-MM-DD HH:mm:ss in GMT+8.
            // Convert from the app's local time (UTC+2) to GMT+8 (add 6 hours).
            $scheduled = \Carbon\Carbon::parse($options['schedule'], 'Africa/Lusaka')
                ->setTimezone('Asia/Kuala_Lumpur')
                ->format('Y-m-d H:i:s');

            $payload['mocean-schedule'] = $scheduled;
        }

        try {
            $response = Http::withToken($this->apiToken)
                ->asForm()
                ->timeout(30)
                ->post("{$this->baseUrl}/sms", $payload);

            $data = $response->json() ?? [];
            Log::info('Mocean SMS response', ['status' => $response->status(), 'body' => $data]);

            $firstMessage = $data['messages'][0] ?? [];
            $moceanStatus = $firstMessage['status'] ?? -1;

            return [
                'success'      => $moceanStatus === 0,
                'responseText' => $this->statusDescription($moceanStatus),
                'statusCode'   => $response->status(),
                'msgid'        => $firstMessage['msgid'] ?? null,
                'raw'          => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('Mocean SMS error: ' . $e->getMessage());
            return [
                'success'      => false,
                'responseText' => 'Mocean request failed: ' . $e->getMessage(),
                'statusCode'   => 500,
                'raw'          => [],
            ];
        }
    }

    /**
     * Normalise a phone number to plain digits with country code.
     *
     * Handles:
     *  - International format already stored  (260973008909  → 260973008909)
     *  - E.164 with leading +                 (+260973008909 → 260973008909)
     *  - Legacy Zambian local format          (0973008909    → 260973008909)
     */
    public static function normalizeNumber(string $number): string
    {
        // Strip everything except digits
        $number = preg_replace('/\D/', '', $number);

        // Legacy local format: starts with 0 but NOT already a full country code (e.g. 260...)
        // If length <= 10, assume a leading-zero local number and prepend Zambia code
        if (str_starts_with($number, '0') && strlen($number) <= 10) {
            $number = '260' . substr($number, 1);
        }

        return $number;
    }

    private function statusDescription(int $code): string
    {
        return match ($code) {
            0  => 'Message sent successfully.',
            1  => 'Authorization failed.',
            2  => 'Insufficient balance.',
            4  => 'Destination number not whitelisted.',
            5  => 'Destination number blacklisted.',
            6  => 'No destination number specified.',
            8  => 'Sender ID not found.',
            26 => 'Invalid schedule format.',
            28 => 'Invalid destination number.',
            29 => 'Message body too long.',
            32 => 'Message throttled.',
            44 => 'Invalid Sender ID.',
            default => "Mocean error code {$code}.",
        };
    }
}
