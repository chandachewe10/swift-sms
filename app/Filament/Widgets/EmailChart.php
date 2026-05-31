<?php

namespace App\Filament\Widgets;

use App\Models\EmailMessage;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\LineChartWidget;
use Illuminate\Database\Eloquent\Builder;

class EmailChart extends LineChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $maxHeight = '200px';
    protected static ?int    $sort      = 6;

    public function getHeading(): string
    {
        return 'Emails Sent';
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate']   ?? null;
        $userId    = auth()->id();

        $records = [];

        for ($month = 1; $month <= 12; $month++) {
            $records[] = EmailMessage::query()
                ->when($startDate, fn (Builder $q) => $q->whereDate('created_at', '>=', $startDate))
                ->when($endDate,   fn (Builder $q) => $q->whereDate('created_at', '<=', $endDate))
                ->where('user_id', $userId)
                ->where('status', 'sent')
                ->whereMonth('created_at', $month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Emails Sent',
                    'data'            => array_map('floatval', $records),
                    'borderColor'     => '#4285F4',
                    'backgroundColor' => 'rgba(66, 133, 244, 0.1)',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
