<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailConfigResource\Pages;
use App\Models\EmailConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailConfigResource extends Resource
{
    protected static ?string $model = EmailConfig::class;
    protected static ?string $navigationGroup = 'Email';
    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'SMTP Config';
    protected static ?string $modelLabel      = 'Email Config';
    protected static ?int    $navigationSort  = 1;

    public static function getNavigationBadge(): ?string
    {
        return 'New';
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return ! EmailConfig::where('user_id', auth()->id())->exists();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Outgoing Mail Server (SMTP)')
                ->schema([
                    Forms\Components\TextInput::make('host')
                        ->label('SMTP Host')
                        ->placeholder('e.g smtp.gmail.com')
                        ->required()
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('port')
                        ->label('Port')
                        ->numeric()
                        ->default(465)
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\Select::make('encryption')
                        ->label('Encryption')
                        ->options([
                            'tls'  => 'TLS (STARTTLS)',
                            'ssl'  => 'SSL',
                            'none' => 'None',
                        ])
                        ->default('tls')
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('username')
                        ->label('SMTP Username')
                        ->placeholder('you@example.com')
                        ->required()
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('password')
                        ->label('SMTP Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->columnSpan(2),
                ])
                ->columns(2),

            Forms\Components\Section::make('Sender Details')
                ->schema([
                    Forms\Components\TextInput::make('from_name')
                        ->label('From Name')
                        ->placeholder('Your Company')
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('from_email')
                        ->label('From Email Address')
                        ->email()
                        ->placeholder('noreply@company.com')
                        ->required()
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
                Tables\Columns\TextColumn::make('host')->label('SMTP Host'),
                Tables\Columns\TextColumn::make('port'),
                Tables\Columns\TextColumn::make('encryption')->badge(),
                Tables\Columns\TextColumn::make('from_email')->label('From'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
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
            'index'  => Pages\ListEmailConfigs::route('/'),
            'create' => Pages\CreateEmailConfig::route('/create'),
            'edit'   => Pages\EditEmailConfig::route('/{record}/edit'),
        ];
    }
}
