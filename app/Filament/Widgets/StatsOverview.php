<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Messages;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {

$startDate = $this->filters['startDate'] ?? null;
$endDate = $this->filters['endDate'] ?? null;
$success = 200;
$companyId = auth()->user()->user_id;
$smsBalance = auth()->user()->wallet->balance;

// Define prefixes for each operator
$airtelPrefixes = ['097', '077'];
$mtnPrefixes = ['096', '076'];
$zamtelPrefixes = ['095', '075'];

return [
    Stat::make('SMS Balance',$smsBalance .' SMSes')
    ->description('Business ID: '.$companyId)
    ->color('info'),
    Stat::make('Airtel', Messages::query()
        ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
        ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
        ->where('status', $success)
        ->where('company_id', $companyId)
        ->where(function ($query) use ($airtelPrefixes) {
            foreach ($airtelPrefixes as $prefix) {
                $query->orWhereRaw("CONCAT(',', contact, ',') LIKE ?", ["%,{$prefix}%"]);
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
                $query->orWhereRaw("CONCAT(',', contact, ',') LIKE ?", ["%,{$prefix}%"]);
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
                $query->orWhereRaw("CONCAT(',', contact, ',') LIKE ?", ["%,{$prefix}%"]);
            }
        })
        ->count())
        ->description('Zamtel')
        ->color('success'),
];

    }
}



