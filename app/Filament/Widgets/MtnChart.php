<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Messages;
use Illuminate\Database\Eloquent\Builder;

class MtnChart extends ChartWidget
{
    protected static ?string $heading = 'Sent MTN Messages';
    protected static ?string $maxHeight = '200px';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $success = 202;  // Example status code
        $companyId = auth()->user()->user_id; // Retrieve the company ID

        // MTN prefixes
        $mtnPrefixes = ['096', '076'];

        // Initialize an array to hold the monthly counts
        $monthlyCounts = array_fill(1, 12, 0);

        // Loop through each month to get counts
        for ($month = 1; $month <= 12; $month++) {
            $monthlyCounts[$month] = Messages::query()
                ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
                ->where(function ($query) use ($mtnPrefixes) {
                    foreach ($mtnPrefixes as $prefix) {
                        $query->orWhere('contact', 'LIKE', "{$prefix}%");
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
        return 'doughnut'; // Use 'doughnut' for a doughnut chart
    }
}
