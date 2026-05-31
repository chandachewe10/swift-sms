<?php

namespace App\Filament\Widgets;

use App\Models\WhatsAppMessage;
use Carbon\CarbonImmutable;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\LineChartWidget;
use Illuminate\Database\Eloquent\Builder;

class WhatsAppChart extends LineChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $maxHeight = '200px';
    protected static ?int    $sort      = 5;

    public function getHeading(): string
    {
        return 'WhatsApp Messages Sent';
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate']   ?? null;
        $userId    = auth()->id();

        $records = [];

        for ($month = 1; $month <= 12; $month++) {
            $records[] = WhatsAppMessage::query()
                ->when($startDate, fn (Builder $q) => $q->whereDate('created_at', '>=', $startDate))
                ->when($endDate,   fn (Builder $q) => $q->whereDate('created_at', '<=', $endDate))
                ->where('user_id', $userId)
                ->whereMonth('created_at', $month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'WhatsApp Messages Sent',
                    'data'            => array_map('floatval', $records),
                    'borderColor'     => '#25D366',
                    'backgroundColor' => 'rgba(37, 211, 102, 0.1)',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
