<?php

namespace App\Api\StarterKits\Filament\Resources\ContactMessages\Presenters\ContactMessages;

use App\Api\StarterKits\Filament\Resources\ContactMessages\Presenters\ContactMessages\Data\ContactMessageData;
use App\Models\Messages as MessagesModel;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use XtendPackages\RESTPresenter\Concerns\InteractsWithPresenter;
use XtendPackages\RESTPresenter\Contracts\Presentable;

class ContactMessagePresenter implements Presentable
{
    use InteractsWithPresenter;

    public function __construct(
        protected Request $request,
        protected ?MessagesModel $model,
    ) {}

    public function transform(): ContactMessageData | Data
    {
        return ContactMessageData::from($this->model);
    }
}
