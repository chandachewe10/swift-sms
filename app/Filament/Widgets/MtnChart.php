<?php

namespace App\Filament\Widgets;

use App\Models\Messages;
use Filament\Widgets\LineChartWidget;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;


class MtnChart extends LineChartWidget
{
    use InteractsWithPageFilters;
   
    protected static ?string $maxHeight = '200px';
    protected static ?int $sort = 3;
   
   



    public function getHeading(): string
    {
        return 'Sent Mtn Messages';
    }

    protected function getData(): array
    {
        $success = 200;  
        $companyId = auth()->user()->user_id; 
    
        
        $mtnPrefixes = ['096', '076'];
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $records = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $records[] = Messages::query()
            ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
            ->where(function ($query) use ($mtnPrefixes) {
                foreach ($mtnPrefixes as $prefix) {
                    $query->orWhereRaw("CONCAT(',', contact, ',') LIKE ?", ["%,{$prefix}%"]);
                }
            })
            ->where('status', $success)
            ->where('company_id', $companyId)
            ->whereMonth('created_at', $month)
            ->count();
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Total Mtn SMS Sent',
                    'data' => array_map('floatval', $records),
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
        


    }

}