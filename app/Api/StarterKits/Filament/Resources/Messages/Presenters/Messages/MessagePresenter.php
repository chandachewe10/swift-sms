<?php

namespace App\Api\StarterKits\Filament\Resources\Messages\Presenters\Messages;

use App\Api\StarterKits\Filament\Resources\Messages\Presenters\Messages\Data\MessageData;
use App\Models\Messages as MessagesModel;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use XtendPackages\RESTPresenter\Concerns\InteractsWithPresenter;
use XtendPackages\RESTPresenter\Contracts\Presentable;

class MessagePresenter implements Presentable
{
    use InteractsWithPresenter;

    public function __construct(
        protected Request $request,
        protected ?MessagesModel $model,
    ) {}

    public function transform(): MessageData | Data
    {
        return MessageData::from($this->model);
    }
}
