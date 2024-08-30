<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Messages;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {

        $startDate = $this->filters['startDate'] ?? null;
$endDate = $this->filters['endDate'] ?? null;
$success = 202;
$companyId = auth()->user()->user_id;

// Define prefixes for each operator
$airtelPrefixes = ['26097', '26077'];
$mtnPrefixes = ['26096', '26076'];
$zamtelPrefixes = ['26095', '26075'];

return [
    Stat::make('Airtel', Messages::query()
        ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
        ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
        ->where('status', $success)
        ->where('company_id', $companyId)
        ->where(function ($query) use ($airtelPrefixes) {
            foreach ($airtelPrefixes as $prefix) {
                $query->orWhere('contact', 'LIKE', "{$prefix}%");
            }
        })
        ->count())
        ->description('Airtel')
        ->color('danger'),

    Stat::make('MTN', Messages::query()
        ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
        ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
        ->where('status', $success)
        ->where('company_id', $companyId)
        ->where(function ($query) use ($mtnPrefixes) {
            foreach ($mtnPrefixes as $prefix) {
                $query->orWhere('contact', 'LIKE', "{$prefix}%");
            }
        })
        ->count())
        ->description('MTN')
        ->color('warning'),

    Stat::make('Zamtel', Messages::query()
        ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
        ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
        ->where('status', $success)
        ->where('company_id', $companyId)
        ->where(function ($query) use ($zamtelPrefixes) {
            foreach ($zamtelPrefixes as $prefix) {
                $query->orWhere('contact', 'LIKE', "{$prefix}%");
            }
        })
        ->count())
        ->description('Zamtel')
        ->color('success'),
];

    }
}
