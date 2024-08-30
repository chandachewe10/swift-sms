<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Messages;
use Illuminate\Database\Eloquent\Builder;

class ZamtelChart extends ChartWidget
{
    
    
    protected static ?string $heading = 'Sent Zamtel Messages';
    protected static ?string $maxHeight = '200px';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $success = 202;
        $companyId = auth()->user()->user_id;
        // Airtel prefixes
        $airtelPrefixes = ['095', '075'];

        // Initialize an array to hold the monthly counts
        $monthlyCounts = array_fill(1, 12, 0);

        // Loop through each month to get counts
        for ($month = 1; $month <= 12; $month++) {
            $monthlyCounts[$month] = Messages::query()
                ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
                ->where(function ($query) use ($airtelPrefixes) {
                    foreach ($airtelPrefixes as $prefix) {
                        $query->orWhere('contact', 'LIKE', "{$prefix}%");
                    }
                })
                ->whereMonth('created_at', $month)
                ->where('status', $success)
                ->where('company_id', $companyId)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'data' => array_map('floatval', $monthlyCounts),
                    'backgroundColor' => [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4CAF50',
                        '#FF8C00',
                        '#9966CC',
                        '#00BFFF',
                        '#FFD700',
                        '#008080',
                        '#FF4500',
                        '#8A2BE2',
                        '#1E90FF',
                    ],
                    'hoverBackgroundColor' => [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4CAF50',
                        '#FF8C00',
                        '#9966CC',
                        '#00BFFF',
                        '#FFD700',
                        '#008080',
                        '#FF4500',
                        '#8A2BE2',
                        '#1E90FF',
                    ],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // Use 'pie' for a pie chart
    }
}
