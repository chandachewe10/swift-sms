<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SendEmailResource\Pages;
use App\Models\EmailMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SendEmailResource extends Resource
{
    protected static ?string $model = EmailMessage::class;
    protected static ?string $navigationGroup = 'Email';
    protected static ?string $navigationIcon  = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Send Email';
    protected static ?string $modelLabel      = 'Email';
    protected static ?int    $navigationSort  = 2;

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
            Forms\Components\Section::make('Compose Email')
                ->schema([
                    Forms\Components\TextInput::make('to_email')
                        ->label('To')
                        ->email()
                        ->required()
                        ->placeholder('recipient@example.com')
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('subject')
                        ->label('Subject')
                        ->required()
                        ->columnSpan(2),

                    Forms\Components\RichEditor::make('body')
                        ->label('Message')
                        ->required()
                        ->columnSpan(2),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->where('user_id', auth()->id())
                ->where('type', 'single'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('to_email')
                    ->label('To')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'sent' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
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
            'index'  => Pages\ListSentEmails::route('/'),
            'create' => Pages\CreateSentEmail::route('/create'),
        ];
    }
}
