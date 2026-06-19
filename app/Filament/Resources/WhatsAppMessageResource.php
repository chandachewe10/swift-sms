<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsAppMessageResource\Pages;
use App\Models\Contact;
use App\Models\WhatsAppConfig;
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
                    Forms\Components\Placeholder::make('config_notice')
                        ->label('')
                        ->content(function (): HtmlString {
                            $hasOwnConfig = (bool) WhatsAppConfig::forUser(auth()->id());

                            if ($hasOwnConfig) {
                                return new HtmlString(
                                    '<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 16px;font-size:14px;color:#1e40af;">'
                                    . 'Sending with your registered company WhatsApp number.'
                                    . '</div>'
                                );
                            }

                            return new HtmlString(
                                '<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;font-size:14px;color:#92400e;">'
                                . '<strong>Note:</strong> You have not registered a WhatsApp phone number yet. '
                                . 'Messages will be sent using the admin testing credentials. '
                                . 'Only approved testing numbers can be used as recipients.'
                                . '</div>'
                            );
                        })
                        ->columnSpan(2),

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

                    Forms\Components\Checkbox::make('send_to_all_contacts')
                        ->label('Send to all contacts with WhatsApp numbers')
                        ->helperText('Targets the Secondary Phone Number (Whatsapp Number) saved on each contact.')
                        ->live()
                        ->columnSpan(2),

                    Forms\Components\Select::make('contact_tag_filter')
                        ->label('Filter contacts by tag')
                        ->options(fn () => Contact::query()
                            ->where('company_id', auth()->user()->user_id)
                            ->whereNotNull('phone2')
                            ->where('phone2', '!=', '')
                            ->distinct()
                            ->orderBy('tag')
                            ->pluck('tag', 'tag')
                            ->filter())
                        ->placeholder('All contacts with WhatsApp numbers')
                        ->native(false)
                        ->visible(fn (Forms\Get $get) => (bool) $get('send_to_all_contacts'))
                        ->columnSpan(2),

                    Forms\Components\Placeholder::make('contacts_preview')
                        ->label('Contacts to receive message')
                        ->content(function (Forms\Get $get): HtmlString {
                            if (! $get('send_to_all_contacts')) {
                                return new HtmlString('<span style="color:#94a3b8;font-size:13px;">Enable the checkbox above to send to saved contacts.</span>');
                            }

                            $count = Contact::query()
                                ->where('company_id', auth()->user()->user_id)
                                ->whereNotNull('phone2')
                                ->where('phone2', '!=', '')
                                ->when($get('contact_tag_filter'), fn ($query, $tag) => $query->where('tag', $tag))
                                ->count();

                            return new HtmlString(
                                '<span style="font-size:14px;color:#166534;">'
                                . e("{$count} contact(s) with WhatsApp numbers will receive this message.")
                                . '</span>'
                            );
                        })
                        ->visible(fn (Forms\Get $get) => (bool) $get('send_to_all_contacts'))
                        ->columnSpan(2),

                    // ── Recipients ────────────────────────────────────────
                    Forms\Components\Repeater::make('recipients')
                        ->label('Manual Recipients')
                        ->helperText(fn (): string => WhatsAppConfig::forUser(auth()->id())
                            ? 'Add individual numbers with country code.'
                            : 'Only testing numbers approved under WhatsApp -> Testing Numbers can be used.')
                        ->schema([
                            Forms\Components\TextInput::make('phone')
                                ->label('Phone number with country code')
                                ->placeholder('e.g. 260971234567')
                                ->required(),
                        ])
                        ->addActionLabel('Add recipient')
                        ->minItems(1)
                        ->visible(fn (Forms\Get $get) => ! $get('send_to_all_contacts'))
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
