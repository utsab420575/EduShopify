<?php

namespace App\Filament\Admin\Resources\ListingResource\Pages;

use App\Filament\Admin\Resources\ListingResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewListing extends ViewRecord
{
    protected static string $resource = ListingResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Listing')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('name'),
                        TextEntry::make('listing_number')->label('Number'),
                        TextEntry::make('supplierAccount.supplierProfile.display_name')->label('Supplier'),
                        TextEntry::make('listing_type')->badge(),
                        TextEntry::make('mainCategory.name')->label('Main category')->placeholder('—'),
                        TextEntry::make('sku')->placeholder('—'),
                    ]),
                ]),

            Section::make('Pricing')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('pricing_type'),
                        TextEntry::make('sales_mode'),
                        TextEntry::make('base_price')
                            ->money(fn ($record) => $record->currency_code ?? 'USD')
                            ->placeholder('Quote only'),
                    ]),
                ]),

            Section::make('Description')
                ->schema([
                    TextEntry::make('short_description')->placeholder('—'),
                    TextEntry::make('description')->placeholder('—')->prose(),
                ]),

            Section::make('Moderation')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('approval_status')->badge(),
                        IconEntry::make('is_active')->boolean(),
                        IconEntry::make('is_featured')->boolean(),
                        TextEntry::make('rejection_reason')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('approvedBy.name')->label('Approved by')->placeholder('—'),
                        TextEntry::make('approved_at')->dateTime()->placeholder('—'),
                    ]),
                ]),
        ]);
    }
}
