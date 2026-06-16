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
                        $config = WhatsAppConfig::first();
                        if (! $config) {
                            Notification::make()->title('WhatsApp credentials not configured')->danger()->send();
                            return;
                        }
                        $service = new WhatsAppService($config->phone_number_id, $config->access_token, $config->business_account_id);
                        $result  = $service->getTemplateStatus($record->name);
                        if (isset($result['data'][0])) {
                            $record->update(['status' => $result['data'][0]['status']]);
                            Notification::make()->title('Status updated to: ' . $result['data'][0]['status'])->success()->send();
                        } else {
                            Notification::make()->title('Could not fetch template status')->body(json_encode($result))->danger()->send();
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
