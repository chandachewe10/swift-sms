<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TokenResource\Pages;
use App\Filament\Resources\TokenResource\RelationManagers;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User as Token;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TokenResource extends Resource
{
    protected static ?string $model = PersonalAccessToken::class;
    protected static ?string $navigationGroup = 'Developers';
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $modelLabel = 'Token';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->label('Token Name')
                ->prefixIcon('heroicon-o-key')
                ->required()
                ->columnSpan(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
       
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTokens::route('/'),
        ];
    }
}
