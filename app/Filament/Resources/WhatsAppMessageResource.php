<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsAppMessageResource\Pages;
use App\Models\Contact;
use App\Models\User;
use App\Models\WhatsAppConfig;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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

    private static function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    private static function resolveOwnerUserId(Get $get): int
    {
        if (self::isSuperAdmin()) {
            return (int) ($get('target_user_id') ?? 0);
        }

        return auth()->id();
    }

    private static function resolveCompanyId(Get $get): ?string
    {
        $ownerId = self::resolveOwnerUserId($get);
        if ($ownerId <= 0) {
            return null;
        }

        return User::find($ownerId)?->user_id;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Business (testing)')
                ->description('Choose which company\'s WhatsApp number and templates to use for this test send.')
                ->icon('heroicon-o-building-office-2')
                ->schema([
                    Forms\Components\Select::make('target_user_id')
                        ->label('Business')
                        ->options(fn () => User::adminWhatsAppBusinessSelectOptions())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            $set('whatsapp_template_id', null);
                            $set('template_preview', null);
                            $set('template_params', []);
                            $set('contact_tag_filter', null);
                        })
                        ->helperText('Only businesses with a registered WhatsApp number are listed.')
                        ->columnSpanFull(),
                ])
                ->visible(fn () => self::isSuperAdmin()),

            Forms\Components\Section::make('Send WhatsApp Message')
                ->schema([
                    Forms\Components\Placeholder::make('config_notice')
                        ->label('')
                        ->content(function (Get $get): HtmlString {
                            if (self::isSuperAdmin()) {
                                $ownerId = self::resolveOwnerUserId($get);
                                if ($ownerId <= 0) {
                                    return new HtmlString(
                                        '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;font-size:14px;color:#64748b;">'
                                        . 'Select a business above to load its WhatsApp credentials and templates.'
                                        . '</div>'
                                    );
                                }
                                $owner = User::find($ownerId);
                                $config = WhatsAppConfig::forUser($ownerId);

                                return new HtmlString(
                                    '<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 16px;font-size:14px;color:#1e40af;">'
                                    . 'Testing send as <strong>' . e($owner?->name ?? 'selected business') . '</strong>'
                                    . ($config?->phone_number ? ' (' . e($config->phone_number) . ').' : '.')
                                    . ' Messages are logged under this business.'
                                    . '</div>'
                                );
                            }

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

                    Forms\Components\Select::make('whatsapp_template_id')
                        ->label('Approved Template')
                        ->options(function (Get $get) {
                            $ownerId = self::resolveOwnerUserId($get);
                            if ($ownerId <= 0) {
                                return [];
                            }

                            return WhatsAppTemplate::availableForUser($ownerId)->pluck('name', 'id');
                        })
                        ->required()
                        ->native(false)
                        ->live()
                        ->disabled(fn (Get $get) => self::isSuperAdmin() && self::resolveOwnerUserId($get) <= 0)
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if (! $state) {
                                $set('template_preview', null);
                                $set('template_params', []);
                                return;
                            }
                            $tpl = WhatsAppTemplate::find($state);
                            if (! $tpl) return;

                            $set('template_preview', $tpl->body_text);

                            $params = $tpl->extractParams();
                            $set('template_params', array_map(
                                fn ($p) => ['param_name' => $p, 'param_value' => ''],
                                $params
                            ));
                        })
                        ->helperText(fn (Get $get) => self::isSuperAdmin()
                            ? 'Approved templates for the selected business (plus shared testing templates when applicable).'
                            : 'Your approved templates plus shared testing templates (opening_our_business_time, system_maintenance)')
                        ->columnSpan(2),

                    Forms\Components\Placeholder::make('template_preview')
                        ->label('Template Body')
                        ->content(fn (Get $get): HtmlString => new HtmlString(
                            $get('template_preview')
                                ? '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;font-size:14px;color:#166534;white-space:pre-wrap;">'
                                    . e($get('template_preview')) . '</div>'
                                : '<span style="color:#94a3b8;font-size:13px;">Select a template to see its body.</span>'
                        ))
                        ->columnSpan(2)
                        ->visible(fn (Get $get) => (bool) $get('whatsapp_template_id')),

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
                        ->visible(fn (Get $get) => ! empty($get('template_params'))),

                    Forms\Components\Checkbox::make('send_to_all_contacts')
                        ->label('Send to all contacts with WhatsApp numbers')
                        ->helperText(fn (Get $get) => self::isSuperAdmin()
                            ? 'Uses the selected business\'s contact list (Secondary Phone / WhatsApp number).'
                            : 'Targets the Secondary Phone Number (Whatsapp Number) saved on each contact.')
                        ->live()
                        ->columnSpan(2),

                    Forms\Components\Select::make('contact_tag_filter')
                        ->label('Filter contacts by tag')
                        ->options(function (Get $get) {
                            $companyId = self::resolveCompanyId($get);
                            if (! $companyId) {
                                return [];
                            }

                            return Contact::query()
                                ->where('company_id', $companyId)
                                ->whereNotNull('phone2')
                                ->where('phone2', '!=', '')
                                ->distinct()
                                ->orderBy('tag')
                                ->pluck('tag', 'tag')
                                ->filter();
                        })
                        ->placeholder('All contacts with WhatsApp numbers')
                        ->native(false)
                        ->visible(fn (Get $get) => (bool) $get('send_to_all_contacts'))
                        ->columnSpan(2),

                    Forms\Components\Placeholder::make('contacts_preview')
                        ->label('Contacts to receive message')
                        ->content(function (Get $get): HtmlString {
                            if (! $get('send_to_all_contacts')) {
                                return new HtmlString('<span style="color:#94a3b8;font-size:13px;">Enable the checkbox above to send to saved contacts.</span>');
                            }

                            $companyId = self::resolveCompanyId($get);
                            if (! $companyId) {
                                return new HtmlString('<span style="color:#94a3b8;font-size:13px;">Select a business to preview contacts.</span>');
                            }

                            $count = Contact::query()
                                ->where('company_id', $companyId)
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
                        ->visible(fn (Get $get) => (bool) $get('send_to_all_contacts'))
                        ->columnSpan(2),

                    Forms\Components\Repeater::make('recipients')
                        ->label('Manual Recipients')
                        ->helperText(function (Get $get): string {
                            if (self::isSuperAdmin()) {
                                $ownerId = self::resolveOwnerUserId($get);
                                if ($ownerId <= 0) {
                                    return 'Select a business first, then add test recipient numbers with country code.';
                                }

                                return 'Add test recipient numbers with country code. Sends use the selected business WhatsApp number.';
                            }

                            return WhatsAppConfig::forUser(auth()->id())
                                ? 'Add individual numbers with country code.'
                                : 'Only testing numbers approved under WhatsApp -> Testing Numbers can be used.';
                        })
                        ->schema([
                            Forms\Components\TextInput::make('phone')
                                ->label('Phone number with country code')
                                ->placeholder('e.g. 260971234567')
                                ->required(),
                        ])
                        ->addActionLabel('Add recipient')
                        ->minItems(1)
                        ->visible(fn (Get $get) => ! $get('send_to_all_contacts'))
                        ->columnSpan(2),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (self::isSuperAdmin()) {
                    $query->with(['user', 'template']);
                } else {
                    $query->where('user_id', auth()->id());
                }
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Business')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => self::isSuperAdmin()),
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
