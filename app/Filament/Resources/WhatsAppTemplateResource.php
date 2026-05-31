<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsAppTemplateResource\Pages;
use App\Models\WhatsAppConfig;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WhatsAppTemplateResource extends Resource
{
    protected static ?string $model = WhatsAppTemplate::class;
    protected static ?string $navigationGroup = 'WhatsApp';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $modelLabel = 'WA Template';
    protected static ?string $navigationLabel = 'Templates';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return 'New';
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Template Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->regex('/^[a-z0-9_]+$/')
                        ->helperText('Lowercase letters, numbers and underscores only (Meta requirement)')
                        ->columnSpan(2),

                    Forms\Components\Select::make('category')
                        ->required()
                        ->options([
                            'MARKETING'      => 'Marketing',
                            'UTILITY'        => 'Utility',
                            'AUTHENTICATION' => 'Authentication',
                        ])
                        ->columnSpan(1),

                    Forms\Components\Select::make('language')
                        ->required()
                        ->options([
                            'en_US' => 'English (US)',
                            'en_GB' => 'English (UK)',
                            'fr'    => 'French',
                            'es'    => 'Spanish',
                            'pt_BR' => 'Portuguese (Brazil)',
                            'ar'    => 'Arabic',
                            'sw'    => 'Swahili',
                        ])
                        ->default('en_US')
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('body_text')
                        ->label('Message Body')
                        ->required()
                        ->rows(4)
                        ->helperText('Use {{1}}, {{2}}, etc. for dynamic variables')
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('status')
                        ->disabled()
                        ->visibleOn('edit')
                        ->columnSpan(1),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', auth()->id()))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\TextColumn::make('language'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'APPROVED' => 'success',
                        'REJECTED' => 'danger',
                        default    => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('checkStatus')
                    ->label('Refresh Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(function (WhatsAppTemplate $record): void {
                        $config = WhatsAppConfig::first();

                        if (! $config) {
                            Notification::make()
                                ->title('WhatsApp credentials not configured')
                                ->body('Go to WhatsApp → API Credentials to add your Meta credentials.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $service = new WhatsAppService(
                            $config->phone_number_id,
                            $config->access_token,
                            $config->business_account_id,
                        );

                        $result = $service->getTemplateStatus($record->name);

                        if (isset($result['data'][0])) {
                            $status = $result['data'][0]['status'];
                            $record->update(['status' => $status]);
                            Notification::make()
                                ->title("Status updated to: {$status}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Could not fetch template status')
                                ->body(json_encode($result))
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWhatsAppTemplates::route('/'),
            'create' => Pages\CreateWhatsAppTemplate::route('/create'),
            'edit'   => Pages\EditWhatsAppTemplate::route('/{record}/edit'),
        ];
    }
}
