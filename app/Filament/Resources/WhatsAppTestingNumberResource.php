<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsAppTestingNumberResource\Pages;
use App\Models\WhatsAppTestingNumber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WhatsAppTestingNumberResource extends Resource
{
    protected static ?string $model = WhatsAppTestingNumber::class;
    protected static ?string $navigationGroup = 'WhatsApp';
    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $modelLabel = 'WA Testing Number';
    protected static ?string $navigationLabel = 'Testing Numbers';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Submit Testing Recipient Number')
                ->description('Add recipient numbers for testing. An admin must approve each number before it can be used by the API.')
                ->schema([
                    Forms\Components\TextInput::make('phone_number')
                        ->label('Phone number with country code')
                        ->placeholder('e.g. 260973750029')
                        ->required()
                        ->maxLength(20),

                    Forms\Components\Textarea::make('admin_note')
                        ->label('Reason / Note (optional)')
                        ->helperText('Optional context for the admin reviewer.')
                        ->rows(2)
                        ->visible(fn () => ! (auth()->user()?->hasRole('super_admin') ?? false)),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $user = auth()->user();
                if ($user?->hasRole('super_admin')) {
                    return $query;
                }

                return $query->where('user_id', auth()->id());
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->toggleable()
                    ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Testing Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->dateTime()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('admin_note')
                    ->label('Admin Note')
                    ->limit(60)
                    ->placeholder('—')
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false)
                    ->requiresConfirmation()
                    ->action(function (WhatsAppTestingNumber $record): void {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        Notification::make()->title('Testing number approved')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false)
                    ->form([
                        Forms\Components\Textarea::make('admin_note')
                            ->label('Rejection note')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (WhatsAppTestingNumber $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'admin_note' => $data['admin_note'],
                        ]);

                        Notification::make()->title('Testing number rejected')->warning()->send();
                    }),
                Tables\Actions\EditAction::make()
                    ->visible(fn (WhatsAppTestingNumber $record) => (auth()->user()?->hasRole('super_admin') ?? false) || $record->status !== 'approved'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (WhatsAppTestingNumber $record) => (auth()->user()?->hasRole('super_admin') ?? false) || $record->status !== 'approved'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsAppTestingNumbers::route('/'),
            'create' => Pages\CreateWhatsAppTestingNumber::route('/create'),
            'edit' => Pages\EditWhatsAppTestingNumber::route('/{record}/edit'),
        ];
    }
}
