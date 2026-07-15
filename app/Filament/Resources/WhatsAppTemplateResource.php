<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsAppTemplateResource\Pages;
use App\Models\WhatsAppConfig;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class WhatsAppTemplateResource extends Resource
{
    protected static ?string $model = WhatsAppTemplate::class;
    protected static ?string $navigationGroup = 'WhatsApp';
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $modelLabel      = 'WA Template';
    protected static ?string $navigationLabel = 'Templates';
    protected static ?int    $navigationSort  = 2;

    public static function getNavigationBadge(): ?string { return 'New'; }
    public static function getNavigationBadgeColor(): string|array|null { return 'warning'; }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private static function extractParams(string $body, string $format): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $body, $matches);
        return array_unique($matches[1] ?? []);
    }

    private static function approvalNotesHtml(): HtmlString
    {
        return new HtmlString(
            '<style>
                .wa-notes-wrap {
                    font-size:13px; line-height:1.6;
                    background:#fffbeb; border:1px solid #fde68a;
                    border-radius:10px; padding:14px 16px;
                }
                .dark .wa-notes-wrap {
                    background:rgba(120,53,15,0.18) !important;
                    border-color:rgba(251,191,36,0.3) !important;
                    color:#e5e7eb !important;
                }
                .wa-notes-title-amber { font-weight:700; font-size:13.5px; margin-bottom:6px; color:#b45309; }
                .wa-notes-title-blue  { font-weight:700; font-size:13.5px; margin-bottom:6px; color:#1d4ed8; }
                .wa-notes-title-green { font-weight:700; font-size:13.5px; margin-bottom:6px; color:#166534; }
                .wa-notes-wrap a { color:#1d4ed8; }
                .dark .wa-notes-title-amber { color:#fbbf24 !important; }
                .dark .wa-notes-title-blue  { color:#93c5fd !important; }
                .dark .wa-notes-title-green { color:#4ade80 !important; }
                .dark .wa-notes-wrap a { color:#93c5fd !important; }
                .dark .wa-notes-wrap strong { color:#f9fafb !important; }
            </style>

            <div class="wa-notes-wrap">

                <div style="margin-bottom:14px;">
                    <div class="wa-notes-title-amber">Message Template Approval Criteria</div>
                    <p style="margin:0 0 6px;">WhatsApp generally rejects a template for one of the following reasons:</p>
                    <ul style="margin:0 0 0 18px;padding:0;">
                        <li>The format is incorrect (for example, misplaced or malformed placeholders).</li>
                        <li>The content violates WhatsApp\'s Terms of Service, Commerce Policy, or Business Policy, or is considered abusive.</li>
                        <li>The template is too generic and includes placeholders that could be used for abuse.</li>
                        <li><strong>Because placeholders can resolve to many words, WhatsApp does not allow a placeholder at the beginning or end of the message. Such placement results in automatic rejection.</strong></li>
                    </ul>
                </div>

                <div style="margin-bottom:14px;">
                    <div class="wa-notes-title-blue">Approval Period</div>
                    <p style="margin:0 0 6px;">After you submit a template, WhatsApp typically approves or rejects it within <strong>minutes</strong> through a machine-learning assisted process. Templates that cannot be triaged automatically are routed for human review and can take <strong>up to 48 hours</strong>.</p>
                    <p style="margin:0;">If a template remains in the <em>Pending</em> state for more than 48 hours, contact SwiftSMS support at <a href="mailto:info@swiftsms.org">info@swiftsms.org</a> and include the template name.</p>
                </div>

                <div>
                    <div class="wa-notes-title-green">Template Statuses</div>
                    <ul style="margin:0 0 0 18px;padding:0;">
                        <li><strong>Pending:</strong> The template is under review. Review can take up to 48 hours.</li>
                        <li><strong>Approved:</strong> The template was approved and can be sent to customers.</li>
                        <li><strong>Rejected:</strong> The template was rejected during review.</li>
                        <li><strong>Paused:</strong> The template was paused because of recurring negative user feedback (for example, blocks or spam reports). Messages that use this template cannot be sent.</li>
                        <li><strong>Disabled:</strong> The template was disabled because of repeated negative feedback or a policy violation. Messages that use this template cannot be sent.</li>
                    </ul>
                </div>

            </div>'
        );
    }

    private static function paramHint(string $format): HtmlString
    {
        if ($format === 'named') {
            return new HtmlString(
                '<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;font-size:13px;color:#1e40af;">
                    <strong>Named format:</strong> use <code>{{first_name}}</code>, <code>{{order_number}}</code>, <code>{{amount}}</code> etc.<br>
                    Parameter names: lowercase letters and underscores only.
                </div>'
            );
        }
        return new HtmlString(
            '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;font-size:13px;color:#166534;">
                <strong>Positional format:</strong> use <code>{{1}}</code>, <code>{{2}}</code>, <code>{{3}}</code> etc.<br>
                Parameters are replaced in order when sending.
            </div>'
        );
    }

    // ── Form ───────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Important Notes Before Submitting')
                ->description('Please read these guidelines carefully before creating your template.')
                ->icon('heroicon-o-information-circle')
                ->iconColor('warning')
                ->collapsible()
                ->schema([
                    Forms\Components\Placeholder::make('approval_notes')
                        ->label('')
                        ->content(fn () => self::approvalNotesHtml())
                        ->columnSpan(2),
                ])
                ->columns(2)
                ->visibleOn('create'),

            Forms\Components\Section::make('Template Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->regex('/^[a-z0-9_]+$/')
                        ->helperText('Lowercase letters, numbers and underscores only (Meta requirement)')
                        ->columnSpan(2),

                    Forms\Components\Select::make('category')
                        ->required()
                        ->options([
                            'MARKETING'      => 'Marketing',
                            'UTILITY'        => 'Utility',
                            'AUTHENTICATION' => 'Authentication',
                        ])
                        ->columnSpan(1),

                    Forms\Components\Select::make('language')
                        ->required()
                        ->options([
                            'en_US' => 'English (US)',
                            'en_GB' => 'English (UK)',
                            'fr'    => 'French',
                            'es'    => 'Spanish',
                            'pt_BR' => 'Portuguese (Brazil)',
                            'ar'    => 'Arabic',
                            'sw'    => 'Swahili',
                        ])
                        ->default('en_US')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('status')
                        ->disabled()
                        ->visibleOn('edit')
                        ->columnSpan(2),
                ])
                ->columns(2),

            Forms\Components\Section::make('Message Body & Parameters')
                ->schema([
                    Forms\Components\Select::make('parameter_format')
                        ->label('Parameter Format')
                        ->options([
                            'positional' => 'Positional — {{1}}, {{2}}, {{3}}',
                            'named'      => 'Named — {{first_name}}, {{amount}}',
                        ])
                        ->default('positional')
                        ->native(false)
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('example_params', []))
                        ->columnSpan(2),

                    Forms\Components\Placeholder::make('format_hint')
                        ->label('')
                        ->content(fn (Forms\Get $get) => self::paramHint($get('parameter_format') ?? 'positional'))
                        ->columnSpan(2),

                    Forms\Components\Textarea::make('body_text')
                        ->label('Message Body')
                        ->required()
                        ->rows(4)
                        ->live(debounce: 600)
                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                            if (! $state) return;
                            $params = self::extractParams($state, $get('parameter_format') ?? 'positional');
                            $set('example_params', array_map(
                                fn ($p) => ['param_name' => $p, 'example_value' => ''],
                                $params
                            ));
                        })
                        ->columnSpan(2),

                    Forms\Components\Repeater::make('example_params')
                        ->label('Example Parameter Values')
                        ->helperText('Meta requires an example value for each parameter so reviewers can understand the template.')
                        ->schema([
                            Forms\Components\TextInput::make('param_name')
                                ->label('Placeholder')
                                ->disabled()
                                ->dehydrated()
                                ->prefix('{{')
                                ->suffix('}}'),

                            Forms\Components\TextInput::make('example_value')
                                ->label('Example Value')
                                ->required()
                                ->placeholder('e.g. John, 500, 2026-01-01'),
                        ])
                        ->columns(2)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columnSpan(2)
                        ->visible(fn (Forms\Get $get) => ! empty($get('example_params'))),
                ])
                ->columns(2),
        ]);
    }

    // ── Table ──────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', auth()->id()))
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('parameter_format')
                    ->label('Format')
                    ->badge()
                    ->color(fn ($state) => $state === 'named' ? 'info' : 'success'),
                Tables\Columns\TextColumn::make('language'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'APPROVED' => 'success',
                        'REJECTED' => 'danger',
                        default    => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('checkStatus')
                    ->label('Refresh Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(function (WhatsAppTemplate $record): void {
                        $config = WhatsAppConfig::forUser(auth()->id());
                        if (! $config || empty($config->phone_number_id) || empty($config->access_token)) {
                            Notification::make()
                                ->title('WhatsApp number not registered')
                                ->body('Register your own WhatsApp Business number before managing templates.')
                                ->danger()->send();
                            return;
                        }
                        $service = new WhatsAppService($config->phone_number_id, $config->access_token, $config->business_account_id);
                        $result  = $service->getTemplateStatus($record->name);
                        if (isset($result['data'][0])) {
                            $record->update(['status' => $result['data'][0]['status']]);
                            Notification::make()->title('Status updated to: ' . $result['data'][0]['status'])->success()->send();
                        } else {
                            $err   = $result['meta_error'] ?? [];
                            $title = $err['error_user_title'] ?? 'Could not fetch template status';
                            $body  = WhatsAppService::friendlyError($err, 'Please check your WhatsApp configuration and try again.');
                            Notification::make()->title($title)->body($body)->danger()->persistent()->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWhatsAppTemplates::route('/'),
            'create' => Pages\CreateWhatsAppTemplate::route('/create'),
            'edit'   => Pages\EditWhatsAppTemplate::route('/{record}/edit'),
        ];
    }
}
