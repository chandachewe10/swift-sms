<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsDeliveryQueueResource\Pages;
use App\Jobs\RetrySmsDeliveryQueueJob;
use App\Models\SmsDeliveryQueue;
use App\Services\SmsDeliveryRetryService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SmsDeliveryQueueResource extends Resource
{
    protected static ?string $model = SmsDeliveryQueue::class;

    protected static ?string $navigationGroup = 'Developers';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'SMS Delivery Queue';

    protected static ?string $modelLabel = 'Queued SMS';

    protected static ?int $navigationSort = 11;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        if (! static::canViewAny()) {
            return null;
        }

        $pending = static::getModel()::query()->where('status', 'pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('company_id')
                    ->label('Company ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sender_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('failed_count')
                    ->label('Pending msgs')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'sent'    => 'success',
                        default   => 'gray',
                    }),
                Tables\Columns\TextColumn::make('message')
                    ->limit(40)
                    ->tooltip(fn (SmsDeliveryQueue $record): ?string => $record->message),
                Tables\Columns\TextColumn::make('provider_response')
                    ->label('Last Zamtel response')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'sent'    => 'Sent',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('retryAllPending')
                    ->label('Retry all pending')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('Resends up to 50 pending rows via Zamtel. Client wallets are not debited again.')
                    ->action(function (SmsDeliveryRetryService $retryService): void {
                        $summary = $retryService->retryPending(50);
                        Notification::make()
                            ->title('Queue retry finished')
                            ->body("Processed {$summary['processed']} row(s). {$summary['sent']} message(s) accepted by Zamtel. {$summary['still_pending']} row(s) still pending.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('dispatchRetryJob')
                    ->label('Queue retry job')
                    ->icon('heroicon-o-queue-list')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('Dispatches a background job to retry up to 50 pending rows.')
                    ->action(function (): void {
                        RetrySmsDeliveryQueueJob::dispatch(null, 50);
                        Notification::make()
                            ->title('Retry job dispatched')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (SmsDeliveryQueue $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (SmsDeliveryQueue $record, SmsDeliveryRetryService $retryService): void {
                        $result = $retryService->retryRecord($record);
                        $record->refresh();

                        Notification::make()
                            ->title($result['success'] ? 'Retry completed' : 'Retry skipped')
                            ->body($result['message'] . ($record->status === 'sent' ? ' Row marked sent.' : ''))
                            ->color($result['success'] ? 'success' : 'warning')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('retrySelected')
                    ->label('Retry selected')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records, SmsDeliveryRetryService $retryService): void {
                        $pending = $records->where('status', 'pending');
                        $sent    = 0;
                        foreach ($pending as $queue) {
                            $outcome = $retryService->retryRecord($queue);
                            $sent += $outcome['sent'];
                        }
                        Notification::make()
                            ->title('Bulk retry finished')
                            ->body("Retried {$pending->count()} row(s). {$sent} message(s) accepted by Zamtel.")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsDeliveryQueues::route('/'),
        ];
    }
}
