<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl = 'https://graph.facebook.com/v22.0';

    /**
     * Convert a Meta API error array into a user-readable string.
     * Prefers Meta's own user-facing fields; falls back to friendly
     * descriptions of common error codes.
     */
    public static function friendlyError(array $metaError, string $default = 'An unexpected error occurred. Please try again.'): string
    {
        if (! empty($metaError['error_user_msg'])) {
            return $metaError['error_user_msg'];
        }

        $code    = $metaError['code']         ?? null;
        $subcode = $metaError['error_subcode'] ?? null;
        $msg     = $metaError['message']       ?? '';

        // Permission / object not found
        if ($code === 100 && $subcode === 33) {
            return 'Your WhatsApp account does not have permission to perform this action. Please reconnect your WhatsApp number from the Register Phone Number page.';
        }

        // Invalid access token
        if ($code === 190) {
            return 'Your WhatsApp access token has expired or is invalid. Please reconnect your WhatsApp number from the Register Phone Number page.';
        }

        // Rate limit
        if ($code === 4 || $code === 32 || $code === 613) {
            return 'Too many requests to WhatsApp. Please wait a few minutes and try again.';
        }

        // Template-specific errors
        if ($code === 100 && $subcode === 2388299) {
            return $msg ?: 'Template variables cannot be at the start or end of the message body.';
        }

        // Generic fallback — strip the developer-facing URL from Meta's message
        if ($msg) {
            return preg_replace('/\s*Please read the Graph API documentation.*$/i', '', $msg);
        }

        return $default;
    }

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
            $body = $response->json() ?? ['error' => ['message' => $response->body()]];
            Log::error('WhatsApp createTemplate error', ['body' => $body]);
            return ['error' => true, 'meta_error' => $body['error'] ?? []];
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
            $body = $response->json() ?? ['error' => ['message' => $response->body()]];
            Log::error('WhatsApp getTemplateStatus error', ['body' => $body]);
            return ['error' => true, 'meta_error' => $body['error'] ?? []];
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
