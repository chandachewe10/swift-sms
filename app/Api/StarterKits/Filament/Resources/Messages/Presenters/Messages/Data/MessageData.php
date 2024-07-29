<?php

namespace App\Api\StarterKits\Filament\Resources\Messages\Presenters\Messages\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\Optional as TypeScriptOptional;

/** @typescript */
class MessageData extends Data
{
    public function __construct(
        public string $id,
		public string $message,
		public string $contact,
		public string $responseText,
		#[TypeScriptOptional]
		public ?Carbon $created_at,
		#[TypeScriptOptional]
		public ?Carbon $updated_at,
		public string $company_id,
		public string $status,
    ) {
    }
}
