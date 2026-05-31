<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BulkContactEmailResource\Pages;
use App\Models\Contact;
use App\Models\EmailMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BulkContactEmailResource extends Resource
{
    protected static ?string $model = EmailMessage::class;
    protected static ?string $navigationGroup = 'Email';
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Bulk Email Contacts';
    protected static ?string $modelLabel      = 'Bulk Email';
    protected static ?int    $navigationSort  = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = Contact::where('company_id', auth()->user()?->user_id)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Compose Bulk Email')
                ->description(function () {
                    $count = Contact::where('company_id', auth()->user()?->user_id)
                        ->whereNotNull('email')
                        ->where('email', '!=', '')
                        ->count();

                    return "This email will be sent to all {$count} contact(s) who have an email address.";
                })
                ->schema([
                    Forms\Components\TextInput::make('subject')
                        ->label('Subject')
                        ->required()
                        ->columnSpan(2),

                    Forms\Components\RichEditor::make('body')
                        ->label('Message Body')
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
                ->where('type', 'bulk'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('to_email')
                    ->label('Recipient')
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
            'index'  => Pages\ListBulkContactEmails::route('/'),
            'create' => Pages\CreateBulkContactEmail::route('/create'),
        ];
    }
}
