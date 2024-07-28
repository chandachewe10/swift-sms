<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessagesResource\Pages;
use App\Filament\Resources\MessagesResource\RelationManagers;
use App\Filament\Imports\MessagesImporter;
use Filament\Tables\Actions\ImportAction;
use App\Models\Messages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MessagesResource extends Resource
{
    protected static ?string $model = Messages::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('message')
                    ->minLength(2)
                    ->maxLength(160)
                    ->rows(5)
                    ->columnSpan(2),

                Forms\Components\Repeater::make('contact')
                    ->label('Phone Number(s)')
                    ->schema([
                        Forms\Components\TextInput::make('contact')
                            ->label('Phone')
                            ->prefixIcon('heroicon-o-phone')
                            ->required()
                            ->maxLength(255)
                            ->tel()
                            ->telRegex('/^(09|07)[5|6|7][0-9]{7}$/')


                    ])
                    ->columnSpan(2)
                    ->addActionLabel('Add Phone number')
                ,

                Forms\Components\TextInput::make('status')
                    ->hidden()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->headerActions([
            ImportAction::make()
                ->importer(MessagesImporter::class)
        ])
            ->columns([
                Tables\Columns\TextColumn::make('message')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact')
                ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('responseText')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(null)
            ->recordAction(null)
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessages::route('/'),
            'create' => Pages\CreateMessages::route('/create'),
            'view' => Pages\ViewMessages::route('/{record}'),
            'edit' => Pages\EditMessages::route('/{record}/edit'),
        ];
    }
}
