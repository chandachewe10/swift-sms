<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl = 'https://graph.facebook.com/v22.0';

    public function __construct(
        protected string $phoneNumberId,
        protected string $accessToken,
        protected ?string $businessAccountId = null,
    ) {}

    /**
     * Submit a new message template to the Meta API.
     */
    public function createTemplate(array $data): array
    {
        $accountId = $this->businessAccountId ?? $this->phoneNumberId;

        $response = Http::withToken($this->accessToken)
            ->post("{$this->baseUrl}/{$accountId}/message_templates", $data);

        if ($response->failed()) {
            Log::error('WhatsApp createTemplate error', ['body' => $response->body()]);
            return ['error' => true, 'message' => $response->body()];
        }

        return $response->json();
    }

    /**
     * Poll Meta for the approval status of a named template.
     */
    public function getTemplateStatus(string $name): array
    {
        $accountId = $this->businessAccountId ?? $this->phoneNumberId;

        $response = Http::withToken($this->accessToken)
            ->get("https://graph.facebook.com/v23.0/{$accountId}/message_templates", [
                'name' => $name,
            ]);

        if ($response->failed()) {
            return ['error' => true, 'message' => $response->body()];
        }

        return $response->json();
    }

    /**
     * Send a template message to a single recipient.
     *
     * @param  string  $to  Phone number with country code, no '+' (e.g. 260971234567)
     * @param  array   $components  Optional template variable components
     */
    public function sendMessage(
        string $to,
        string $templateName,
        string $languageCode = 'en_US',
        array $components = [],
    ): array {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        $response = Http::withToken($this->accessToken)
            ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", $payload);

        if ($response->failed()) {
            Log::error('WhatsApp sendMessage error', ['to' => $to, 'body' => $response->body()]);
            return ['error' => true, 'message' => $response->body()];
        }

        return $response->json();
    }
}
