<?php
namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Card;

class Payments extends Page
{
    protected static string $view = 'filament.pages.payments';
    protected static ?string $navigationGroup = 'Transactions';
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $modelLabel = 'Make Payments';
    protected function getCards(): array
    {
        return [
            Card::make('Basic Package','122')
                ->description('Price: $10')
                ->url('https://www.google.com') // Redirection URL
                ->color('blue')
                ->icon('heroicon-o-cash'),

            Card::make('Standard Package','123')
                ->description('Price: $20')
                ->url('/payment/standard') // Redirection URL
                ->color('green')
                ->icon('heroicon-o-cash'),

            Card::make('Premium Package','233')
                ->description('Price: $30')
                ->url('/payment/premium') // Redirection URL
                ->color('yellow')
                ->icon('heroicon-o-cash'),

            Card::make('Pro Package','12')
                ->description('Price: $40')
                ->url('/payment/pro') // Redirection URL
                ->color('red')
                ->icon('heroicon-o-cash'),

            Card::make('Enterprise Package','23')
                ->description('Price: $50')
                ->url('/payment/enterprise') // Redirection URL
                ->color('purple')
                ->icon('heroicon-o-cash'),

            Card::make('Ultimate Package','10')
                ->description('Price: $60')
                ->url('/payment/ultimate') // Redirection URL
                ->color('orange')
                ->icon('heroicon-o-cash'),
        ];
    }
}
