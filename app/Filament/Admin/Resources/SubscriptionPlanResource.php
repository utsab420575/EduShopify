<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubscriptionPlanResource\Pages;
use App\Models\Currency;
use App\Models\SubscriptionPlan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel  = 'Subscription Plans';
    protected static ?string $pluralModelLabel = 'Subscription Plans';
    protected static string|\UnitEnum|null $navigationGroup = 'Subscriptions';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        $defaultCurrency = Currency::where('is_default', true)->value('code') ?? 'USD';

        return $schema->schema([

            /* ── Basic Info ── */
            Section::make('Plan Identity')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(100)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),

                    Select::make('billing_type')
                        ->options([
                            'free'    => 'Free',
                            'monthly' => 'Monthly',
                            'yearly'  => 'Yearly',
                        ])
                        ->required()
                        ->live(),

                    TextInput::make('sort_order')
                        ->label('Display Order')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    Toggle::make('is_featured')
                        ->label('Featured Plan (Best Value badge)')
                        ->default(false),

                    Toggle::make('is_active')
                        ->label('Active (visible on pricing page)')
                        ->default(true),
                ])
                ->columns(2),

            /* ── Pricing ── */
            Section::make('Pricing')
                ->schema([
                    TextInput::make('price')
                        ->numeric()
                        ->prefix(fn ($get) => Currency::where('code', $get('currency_code') ?: $defaultCurrency)->value('symbol') ?? '$')
                        ->default(0)
                        ->minValue(0)
                        ->hidden(fn ($get) => $get('billing_type') === 'free'),

                    Select::make('currency_code')
                        ->label('Currency')
                        ->options(Currency::where('is_active', true)->pluck('code', 'code'))
                        ->default($defaultCurrency)
                        ->searchable()
                        ->hidden(fn ($get) => $get('billing_type') === 'free'),

                    TextInput::make('stripe_price_id')
                        ->label('Stripe Price ID')
                        ->placeholder('price_xxxxxxxxxxxx')
                        ->helperText('Copy from your Stripe Dashboard → Products → Price ID')
                        ->hidden(fn ($get) => $get('billing_type') === 'free'),
                ])
                ->columns(2),

            /* ── Trial & Bonus Days ── */
            Section::make('Trial & Bonus Days')
                ->schema([
                    TextInput::make('trial_days')
                        ->label('Trial Days')
                        ->helperText('Number of free days for this plan (free plans only)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->visible(fn ($get) => $get('billing_type') === 'free'),

                    TextInput::make('bonus_days')
                        ->label('Bonus Days')
                        ->helperText('Extra free days added on top (shows as badge on pricing card)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                ])
                ->columns(2),

            /* ── Limits ── */
            Section::make('Usage Limits')
                ->schema([
                    TextInput::make('max_active_listings')
                        ->label('Max Listings')
                        ->helperText('0 = unlimited')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    TextInput::make('max_products')
                        ->label('Max Products')
                        ->helperText('0 = unlimited')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    TextInput::make('rfq_delay_minutes')
                        ->label('RFQ Delay (minutes)')
                        ->helperText('0 = instant access; higher = delayed access to new RFQs')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                ])
                ->columns(3),

            /* ── Feature Toggles ── */
            Section::make('Features & Perks')
                ->schema([
                    Toggle::make('has_rfq_notifications')
                        ->label('RFQ Email Notifications'),

                    Toggle::make('has_analytics')
                        ->label('Analytics Dashboard'),

                    Toggle::make('has_verified_badge')
                        ->label('Verified Supplier Badge'),

                    Toggle::make('has_homepage_placement')
                        ->label('Featured on Homepage'),

                    Toggle::make('has_team_members')
                        ->label('Multiple Team Members'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('billing_type')
                    ->badge()
                    ->colors([
                        'success' => 'free',
                        'primary' => 'monthly',
                        'warning' => 'yearly',
                    ])
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                TextColumn::make('price')
                    ->formatStateUsing(function ($record) {
                        if ($record->isFree()) return '—';
                        $symbol = Currency::where('code', $record->currency_code)->value('symbol') ?? '$';
                        return $symbol . number_format((float) $record->price, 2);
                    })
                    ->label('Price'),

                TextColumn::make('bonus_days')
                    ->label('Bonus Days')
                    ->formatStateUsing(fn (int $state) => $state > 0 ? "+{$state} days" : '—')
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('trial_days')
                    ->label('Trial Days')
                    ->formatStateUsing(fn ($state, $record) => $record->isFree() ? "{$state}d" : '—'),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('subscriptions_count')
                    ->label('Subscribers')
                    ->counts('subscriptions')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('billing_type')
                    ->options([
                        'free'    => 'Free',
                        'monthly' => 'Monthly',
                        'yearly'  => 'Yearly',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit'   => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
