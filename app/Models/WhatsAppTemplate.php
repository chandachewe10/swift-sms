<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppTemplate extends Model
{
    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'language',
        'body_text',
        'parameter_format',
        'status',
        'whatsapp_template_id',
    ];

    /**
     * Extract parameter names/positions from the body text.
     * Named:      {{first_name}} → ['first_name', 'order_number']
     * Positional: {{1}}, {{2}}   → ['1', '2']
     */
    public function extractParams(): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $this->body_text ?? '', $matches);
        return array_unique($matches[1] ?? []);
    }

    public function hasParams(): bool
    {
        return count($this->extractParams()) > 0;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class);
    }
}
