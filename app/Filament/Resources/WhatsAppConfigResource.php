<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsAppConfigResource\Pages;
use App\Models\WhatsAppConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WhatsAppConfigResource extends Resource
{
    protected static ?string $model = WhatsAppConfig::class;
    protected static ?string $navigationGroup = 'WhatsApp';
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $modelLabel = 'WA Credentials';
    protected static ?string $navigationLabel = 'API Credentials';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return ! WhatsAppConfig::where('user_id', auth()->id())->exists();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Meta WhatsApp Cloud API')
                ->description('Credentials from Meta Business Suite → WhatsApp → API Setup')
                ->schema([
                    Forms\Components\TextInput::make('phone_number_id')
                        ->label('Phone Number ID')
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('business_account_id')
                        ->label('WhatsApp Business Account ID (WABA)')
                        ->helperText('Required for template management')
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('access_token')
                        ->label('Permanent Access Token')
                        ->required()
                        ->rows(3)
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('app_id')
                        ->label('App ID')
                        ->helperText('Optional')
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
                Tables\Columns\TextColumn::make('phone_number_id')
                    ->label('Phone Number ID'),
                Tables\Columns\TextColumn::make('business_account_id')
                    ->label('WABA ID')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('app_id')
                    ->label('App ID')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWhatsAppConfigs::route('/'),
            'create' => Pages\CreateWhatsAppConfig::route('/create'),
            'edit'   => Pages\EditWhatsAppConfig::route('/{record}/edit'),
        ];
    }
}
