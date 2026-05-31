<?php
 
namespace App\Filament\Pages;

use App\Filament\Widgets\AirtelChart;
use App\Filament\Widgets\EmailChart;
use App\Filament\Widgets\MtnChart;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\WhatsAppChart;
use App\Filament\Widgets\ZamtelChart;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
 
class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersAction;

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->form([
                    DatePicker::make('startDate'),
                    DatePicker::make('endDate'),
                ]),
        ];
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            AirtelChart::class,
            MtnChart::class,
            ZamtelChart::class,
            WhatsAppChart::class,
            EmailChart::class,
        ];
    }
}