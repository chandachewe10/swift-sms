<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsTemplateResource\Pages;
use App\Models\SmsTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class SmsTemplateResource extends Resource
{
    protected static ?string $model = SmsTemplate::class;
    protected static ?string $navigationGroup = 'Messages';
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $modelLabel      = 'SMS Template';
    protected static ?int    $navigationSort  = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Template Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Template Name')
                        ->placeholder('e.g. Loan Reminder, OTP Verification, Welcome Message')
                        ->required()
                        ->maxLength(100)
                        ->columnSpan(2),

                    Forms\Components\Select::make('category')
                        ->label('Category')
                        ->options([
                            'Marketing'      => 'Marketing',
                            'Transactional'  => 'Transactional',
                            'OTP'            => 'OTP / Verification',
                            'Finance'        => 'Finance',
                            'Reminder'       => 'Reminder',
                            'Notification'   => 'Notification',
                            'Other'          => 'Other',
                        ])
                        ->native(false)
                        ->placeholder('Select category')
                        ->columnSpan(2),
                ])
                ->columns(2),

            Forms\Components\Section::make('Message Body')
                ->description(new HtmlString(
                    'Use curly-brace placeholders for dynamic content — e.g. <code>{name}</code>, <code>{amount}</code>, <code>{company}</code>, <code>{date}</code>, <code>{otp}</code>.<br>'
                    . 'When sending to <strong>contacts</strong>, <code>{name}</code> is automatically replaced with the contact\'s name.'
                ))
                ->schema([
                    Forms\Components\Textarea::make('body')
                        ->label('Message')
                        ->placeholder("Dear {name}, your payment of K{amount} is due on {date}. Reply STOP to unsubscribe.")
                        ->required()
                        ->rows(5)
                        ->maxLength(160)
                        ->live()
                        ->helperText(fn ($state) => self::bodyHelperText($state))
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('placeholder_hint')
                        ->label('Detected Placeholders')
                        ->content(fn (Forms\Get $get) => self::placeholderBadges($get('body')))
                        ->columnSpanFull(),
                ]),
        ]);
    }

    private static function bodyHelperText(?string $body): string
    {
        $len = mb_strlen($body ?? '');
        return "{$len}/160 characters";
    }

    private static function placeholderBadges(?string $body): HtmlString
    {
        if (! $body) {
            return new HtmlString('<span style="color:#94a3b8;font-size:13px;">No placeholders detected yet.</span>');
        }

        preg_match_all('/\{(\w+)\}/', $body, $matches);
        $tokens = array_unique($matches[1] ?? []);

        if (empty($tokens)) {
            return new HtmlString('<span style="color:#94a3b8;font-size:13px;">No placeholders detected.</span>');
        }

        $badges = implode(' ', array_map(
            fn ($t) => "<span style='display:inline-block;background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;margin:2px;'>{" . e($t) . "}</span>",
            $tokens
        ));

        $autoNote = in_array('name', $tokens)
            ? '<div style="margin-top:8px;font-size:12px;color:#16a34a;">✅ <strong>{name}</strong> will be auto-filled from the contact\'s name when sending to contacts.</div>'
            : '';

        return new HtmlString($badges . $autoNote);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('company_id', auth()->user()->user_id))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Template Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('category')
                    ->label('Category')
                    ->colors([
                        'primary'   => 'Marketing',
                        'success'   => 'Transactional',
                        'warning'   => 'OTP',
                        'info'      => 'Finance',
                        'secondary' => 'Reminder',
                        'danger'    => 'Notification',
                    ]),

                Tables\Columns\TextColumn::make('body')
                    ->label('Preview')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->body),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('No SMS templates yet')
            ->emptyStateDescription('Create reusable message templates with placeholders like {name} and {amount}.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Create Template'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSmsTemplates::route('/'),
            'create' => Pages\CreateSmsTemplate::route('/create'),
            'edit'   => Pages\EditSmsTemplate::route('/{record}/edit'),
        ];
    }
}
