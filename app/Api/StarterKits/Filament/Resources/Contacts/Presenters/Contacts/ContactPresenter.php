<?php

namespace App\Api\StarterKits\Filament\Resources\Contacts\Presenters\Contacts;

use App\Api\StarterKits\Filament\Resources\Contacts\Presenters\Contacts\Data\ContactData;
use App\Models\Contact as ContactModel;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use XtendPackages\RESTPresenter\Concerns\InteractsWithPresenter;
use XtendPackages\RESTPresenter\Contracts\Presentable;

class ContactPresenter implements Presentable
{
    use InteractsWithPresenter;

    public function __construct(
        protected Request $request,
        protected ?ContactModel $model,
    ) {}

    public function transform(): ContactData | Data
    {
        return ContactData::from($this->model);
    }
}
