<?php

namespace App\Filament\Widgets;

use App\Models\EmailMessage;
use App\Models\Messages;
use App\Models\WhatsAppMessage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate']   ?? null;
        $companyId = auth()->user()->user_id;
        $userId    = auth()->id();
        $smsBalance = auth()->user()->wallet->balance;

        $airtelPrefixes = ['097', '077'];
        $mtnPrefixes    = ['096', '076'];
        $zamtelPrefixes = ['095', '075'];

        $whatsappSent = WhatsAppMessage::query()
            ->when($startDate, fn (Builder $q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate,   fn (Builder $q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('user_id', $userId)
            ->count();

        $emailsSent = EmailMessage::query()
            ->when($startDate, fn (Builder $q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate,   fn (Builder $q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('user_id', $userId)
            ->where('status', 'sent')
            ->count();

        return [
            Stat::make('SMS Balance', $smsBalance . ' SMSes')
                ->description('Business ID: ' . $companyId)
                ->color('info'),

            Stat::make('Airtel', Messages::query()
                ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                ->when($endDate,   fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
                ->where('status', 200)
                ->where('company_id', $companyId)
                ->where(function ($query) use ($airtelPrefixes) {
                    foreach ($airtelPrefixes as $prefix) {
                        $query->orWhereRaw("CONCAT(',', contact, ',') LIKE ?", ["%,{$prefix}%"]);
                    }
                })
                ->count())
                ->description('Airtel SMS sent')
                ->color('danger'),

            Stat::make('MTN', Messages::query()
                ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                ->when($endDate,   fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
                ->where('status', 200)
                ->where('company_id', $companyId)
                ->where(function ($query) use ($mtnPrefixes) {
                    foreach ($mtnPrefixes as $prefix) {
                        $query->orWhereRaw("CONCAT(',', contact, ',') LIKE ?", ["%,{$prefix}%"]);
                    }
                })
                ->count())
                ->description('MTN SMS sent')
                ->color('warning'),

            Stat::make('Zamtel', Messages::query()
                ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                ->when($endDate,   fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
                ->where('status', 200)
                ->where('company_id', $companyId)
                ->where(function ($query) use ($zamtelPrefixes) {
                    foreach ($zamtelPrefixes as $prefix) {
                        $query->orWhereRaw("CONCAT(',', contact, ',') LIKE ?", ["%,{$prefix}%"]);
                    }
                })
                ->count())
                ->description('Zamtel SMS sent')
                ->color('success'),

            Stat::make('WhatsApp Sent', $whatsappSent)
                ->description('WhatsApp messages sent')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('success'),

            Stat::make('Emails Sent', $emailsSent)
                ->description('Emails delivered successfully')
                ->icon('heroicon-o-envelope')
                ->color('info'),
        ];
    }
}



