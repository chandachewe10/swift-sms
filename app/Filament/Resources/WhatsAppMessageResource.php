<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsAppMessageResource\Pages;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WhatsAppMessageResource extends Resource
{
    protected static ?string $model = WhatsAppMessage::class;
    protected static ?string $navigationGroup = 'WhatsApp';
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $modelLabel = 'WA Message';
    protected static ?string $navigationLabel = 'Send Message';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Send WhatsApp Message')
                ->schema([
                    Forms\Components\Select::make('whatsapp_template_id')
                        ->label('Approved Template')
                        ->options(fn () => WhatsAppTemplate::where('user_id', auth()->id())
                            ->where('status', 'APPROVED')
                            ->pluck('name', 'id'))
                        ->required()
                        ->helperText('Only Meta-approved templates can be sent')
                        ->columnSpan(2),

                    Forms\Components\Repeater::make('recipients')
                        ->label('Recipients')
                        ->schema([
                            Forms\Components\TextInput::make('phone')
                                ->label('Phone number with country code')
                                ->placeholder('e.g. 260971234567')
                                ->required(),
                        ])
                        ->addActionLabel('Add recipient')
                        ->minItems(1)
                        ->columnSpan(2),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', auth()->id()))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('template.name')
                    ->label('Template')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recipient_phone')
                    ->label('Recipient')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'queued'  => 'success',
                        'failed'  => 'danger',
                        default   => 'warning',
                    }),
                Tables\Columns\TextColumn::make('whatsapp_message_id')
                    ->label('Message ID')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('error_message')
                    ->label('Error')
                    ->placeholder('—')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index'  => Pages\ListWhatsAppMessages::route('/'),
            'create' => Pages\CreateWhatsAppMessage::route('/create'),
        ];
    }
}
