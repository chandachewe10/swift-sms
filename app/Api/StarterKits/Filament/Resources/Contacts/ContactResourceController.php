<?php

namespace App\Api\StarterKits\Filament\Resources\Contacts;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use XtendPackages\RESTPresenter\Resources\ResourceController;

class ContactResourceController extends ResourceController
{
    protected static string $model = Contact::class;

    public static bool $isAuthenticated = false;

    public function index(Request $request): Collection
    {
        $contacts = $this->getModelQueryInstance()->get();

        return $contacts->map(
            fn (Contact $contact) => $this->present($request, $contact),
        );
    }

    public function show(Request $request, Contact $contact): Data
    {
        return $this->present($request, $contact);
    }

    public function filters(): array
    {
        return [
            
        ];
    }

    public function presenters(): array
    {
        return [
            'contact' => Presenters\Contacts\ContactPresenter::class,
        ];
    }
}
