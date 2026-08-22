<?php

namespace App\Filament\Admin\Resources\RfqResource\Pages;

use App\Filament\Admin\Resources\RfqResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewRfq extends ViewRecord
{
    protected static string $resource = RfqResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('RFQ')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('rfq_number'),
                        TextEntry::make('title'),
                        TextEntry::make('buyerAccount.buyerProfile.display_name')->label('Buyer'),
                        TextEntry::make('visibility_type')->badge(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('quotation_deadline')->dateTime(),
                    ]),
                    TextEntry::make('description')->placeholder('—')->prose(),
                ]),

            Section::make('Items')
                ->schema([
                    RepeatableEntry::make('items')
                        ->label('')
                        ->schema([
                            Grid::make(3)->schema([
                                TextEntry::make('item_name'),
                                TextEntry::make('quantity'),
                                TextEntry::make('item_type')->badge(),
                            ]),
                        ]),
                ]),
        ]);
    }
}
