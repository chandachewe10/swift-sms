<?php

namespace App\Api\StarterKits\Filament\Resources\Contacts\Presenters\Contacts\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\Optional as TypeScriptOptional;

/** @typescript */
class ContactData extends Data
{
    public function __construct(
        public string $id,
		public string $company_id,
		public string $first_name,
		public string $last_name,
		public string $phone1,
		#[TypeScriptOptional]
		public ?string $email,
		#[TypeScriptOptional]
		public ?string $address,
		#[TypeScriptOptional]
		public ?string $tag,
		#[TypeScriptOptional]
		public ?Carbon $created_at,
		#[TypeScriptOptional]
		public ?Carbon $updated_at,
		#[TypeScriptOptional]
		public ?string $phone2,
		#[TypeScriptOptional]
		public ?string $phone3,
		#[TypeScriptOptional]
		public ?string $company,
		#[TypeScriptOptional]
		public ?string $nationality,
    ) {
    }
}
