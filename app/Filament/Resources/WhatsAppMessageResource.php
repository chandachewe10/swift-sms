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
use Illuminate\Support\HtmlString;

class WhatsAppMessageResource extends Resource
{
    protected static ?string $model = WhatsAppMessage::class;
    protected static ?string $navigationGroup = 'WhatsApp';
    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $modelLabel      = 'WA Message';
    protected static ?string $navigationLabel = 'Send Message';
    protected static ?int    $navigationSort  = 3;

    public static function getNavigationBadge(): ?string { return 'New'; }
    public static function getNavigationBadgeColor(): string|array|null { return 'warning'; }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Send WhatsApp Message')
                ->schema([

                    // ── Template selection ────────────────────────────────
                    Forms\Components\Select::make('whatsapp_template_id')
                        ->label('Approved Template')
                        ->options(fn () => WhatsAppTemplate::query()
                            ->where('status', 'APPROVED')
                            ->where(function ($query) {
                                $query->where('user_id', auth()->id())
                                    ->orWhereIn('name', WhatsAppTemplate::SHARED_TESTING_TEMPLATES);
                            })
                            ->pluck('name', 'id'))
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if (! $state) {
                                $set('template_preview', null);
                                $set('template_params', []);
                                return;
                            }
                            $tpl = WhatsAppTemplate::find($state);
                            if (! $tpl) return;

                            $set('template_preview', $tpl->body_text);

                            // Pre-populate parameter rows
                            $params = $tpl->extractParams();
                            $set('template_params', array_map(
                                fn ($p) => ['param_name' => $p, 'param_value' => ''],
                                $params
                            ));
                        })
                        ->helperText('Your approved templates plus shared testing templates (opening_our_business_time, system_maintenance)')
                        ->columnSpan(2),

                    // ── Template body preview ─────────────────────────────
                    Forms\Components\Placeholder::make('template_preview')
                        ->label('Template Body')
                        ->content(fn (Forms\Get $get): HtmlString => new HtmlString(
                            $get('template_preview')
                                ? '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;font-size:14px;color:#166534;white-space:pre-wrap;">'
                                    . e($get('template_preview')) . '</div>'
                                : '<span style="color:#94a3b8;font-size:13px;">Select a template to see its body.</span>'
                        ))
                        ->columnSpan(2)
                        ->visible(fn (Forms\Get $get) => (bool) $get('whatsapp_template_id')),

                    // ── Parameter values ──────────────────────────────────
                    Forms\Components\Repeater::make('template_params')
                        ->label('Parameter Values')
                        ->helperText('Fill in the value for each placeholder that will appear in the message.')
                        ->schema([
                            Forms\Components\TextInput::make('param_name')
                                ->label('Placeholder')
                                ->disabled()
                                ->dehydrated()
                                ->prefix('{{')
                                ->suffix('}}'),

                            Forms\Components\TextInput::make('param_value')
                                ->label('Value to Insert')
                                ->required()
                                ->placeholder('Enter the actual value…'),
                        ])
                        ->columns(2)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columnSpan(2)
                        ->visible(fn (Forms\Get $get) => ! empty($get('template_params'))),

                    // ── Recipients ────────────────────────────────────────
                    Forms\Components\Repeater::make('recipients')
                        ->label('Recipients')
                        ->helperText('Only testing numbers approved by admin in WhatsApp -> Testing Numbers can be used.')
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
                Tables\Columns\TextColumn::make('template.name')->label('Template')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('recipient_phone')->label('Recipient')->badge()->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'queued'  => 'success',
                        'failed'  => 'danger',
                        default   => 'warning',
                    }),
                Tables\Columns\TextColumn::make('whatsapp_message_id')->label('Message ID')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('error_message')->label('Error')->placeholder('—')->limit(60)->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('Sent At')->dateTime()->sortable(),
            ])
            ->actions([Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWhatsAppMessages::route('/'),
            'create' => Pages\CreateWhatsAppMessage::route('/create'),
        ];
    }
}
