<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    protected $fillable = ['company_id', 'name', 'body', 'category'];

    /**
     * Return all placeholder tokens found in the body, e.g. ['name','amount','date'].
     */
    public function placeholders(): array
    {
        preg_match_all('/\{(\w+)\}/', $this->body, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Replace placeholders in the body with given values.
     * Missing values are left as-is so the user can spot them.
     */
    public function render(array $values = []): string
    {
        $body = $this->body;
        foreach ($values as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }
        return $body;
    }
}
