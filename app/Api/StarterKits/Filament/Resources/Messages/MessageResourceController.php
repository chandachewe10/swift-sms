<?php

namespace App\Api\StarterKits\Filament\Resources\Messages;

use App\Models\Messages;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use XtendPackages\RESTPresenter\Resources\ResourceController;

class MessageResourceController extends ResourceController
{
    protected static string $model = Messages::class;

    public static bool $isAuthenticated = false;

    public function index(Request $request): Collection
    {
        $messages = $this->getModelQueryInstance()->get();

        return $messages->map(
            fn (Messages $messages) => $this->present($request, $messages),
        );
    }

    public function show(Request $request, Messages $messages): Data
    {
        return $this->present($request, $messages);
    }

    public function filters(): array
    {
        return [
            
        ];
    }

    public function presenters(): array
    {
        return [
            'message' => Presenters\Messages\MessagePresenter::class,
        ];
    }
}
